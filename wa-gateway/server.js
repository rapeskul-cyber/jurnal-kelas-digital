// ========================================================
// Real Baileys WhatsApp Gateway Service (Production Ready v2.0)
// Integrated with Jurnal Kelas Digital
// Port: 3000
// ========================================================

const http = require('http');
const path = require('path');
const fs = require('fs');
const {
  default: makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestWaWebVersion,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
  Browsers,
  delay,
} = require('@whiskeysockets/baileys');
const pino = require('pino');

const PORT = process.env.PORT || 3000;
const SESSION_DIR = path.join(__dirname, 'session_auth');

if (!fs.existsSync(SESSION_DIR)) {
  fs.mkdirSync(SESSION_DIR, { recursive: true });
}

function clearSession() {
  try {
    if (fs.existsSync(SESSION_DIR)) {
      fs.rmSync(SESSION_DIR, { recursive: true, force: true });
      fs.mkdirSync(SESSION_DIR, { recursive: true });
    }
  } catch (e) {
    console.error('[BAILEYS] Error clearing session:', e);
  }
}

function formatPhoneNumber(phone) {
  if (!phone) return '';
  let clean = phone.replace(/[^0-9]/g, '');
  if (clean.startsWith('0')) {
    clean = '62' + clean.slice(1);
  }
  return clean;
}

let sock = null;
let isStarting = false;
let pairingInFlight = false;
let pendingPhone = null;
let reconnectTimer = null;
let cachedWaVersion = null;

async function getWaVersion() {
  if (cachedWaVersion) return cachedWaVersion;
  try {
    const waWeb = await fetchLatestWaWebVersion();
    cachedWaVersion = waWeb.version;
    console.log('[BAILEYS] Menggunakan WA Web version terbaru:', cachedWaVersion);
  } catch (e) {
    try {
      const fallback = await fetchLatestBaileysVersion();
      cachedWaVersion = fallback.version;
      console.log('[BAILEYS] Fallback Baileys version:', cachedWaVersion);
    } catch {
      cachedWaVersion = [2, 3000, 1015901307];
    }
  }
  return cachedWaVersion;
}

// State perangkat (Status connected HANYA true jika connection === 'open' & sock.user.id valid)
let deviceState = {
  connected: false,
  status: 'disconnected', // 'disconnected' | 'connecting' | 'connected'
  phone: null,
  user: null,
  pairingCode: null,
  pairingCodeAt: 0,
  pairedAt: null,
};

const PAIRING_TTL_MS = 3 * 60 * 1000; // 3 menit TTL

async function startBaileys() {
  if (isStarting) return;
  isStarting = true;

  if (reconnectTimer) {
    clearTimeout(reconnectTimer);
    reconnectTimer = null;
  }

  if (sock) {
    try { sock.ev.removeAllListeners(); } catch (e) {}
    try { sock.ws?.close(); } catch (e) {}
    try { sock.end(undefined); } catch (e) {}
    sock = null;
  }
  await delay(200);

  try {
    const { state, saveCreds } = await useMultiFileAuthState(SESSION_DIR);
    const logger = pino({ level: 'silent' });
    const version = await getWaVersion();

    const current = makeWASocket({
      version,
      logger,
      printQRInTerminal: false,
      auth: {
        creds: state.creds,
        keys: makeCacheableSignalKeyStore(state.keys, logger),
      },
      browser: Browsers.ubuntu('Chrome'),
      connectTimeoutMs: 60000,
      defaultQueryTimeoutMs: 0,
      keepAliveIntervalMs: 15000,
      emitOwnEvents: true,
      fireInitQueries: true,
      syncFullHistory: false,
      markOnlineOnConnect: true,
      ...(pairingInFlight ? { qrTimeout: 300000 } : {}),
    });
    sock = current;

    current.ev.process(async (events) => {
      // 1. Simpan update kredensial
      if (events['creds.update']) {
        await saveCreds();
      }

      // 2. Lifecycle koneksi
      if (events['connection.update']) {
        if (sock !== current) return; // Abaikan event socket lama

        const update = events['connection.update'];
        const { connection, lastDisconnect } = update;

        if (connection === 'connecting') {
          if (!deviceState.connected) {
            deviceState.status = deviceState.pairingCode ? 'connecting' : 'disconnected';
          }
          console.log('[BAILEYS] Menghubungkan ke WebSocket server WhatsApp Meta...');
        }

        if (connection === 'open') {
          const rawId = current.user?.id ? current.user.id.split(':')[0] : (pendingPhone || 'Unknown');
          const userId = rawId.replace(/[^0-9]/g, '');
          console.log(`[BAILEYS] >>> WHATSAPP BERHASIL TERHUBUNG VALID! Nomor: ${userId}`);

          deviceState.connected = true;
          deviceState.status = 'connected';
          deviceState.phone = userId;
          deviceState.user = {
            id: current.user?.id || `${userId}@s.whatsapp.net`,
            name: current.user?.name || 'Bot Jurnal Kelas Digital',
          };
          deviceState.pairedAt = new Date().toISOString();
          deviceState.pairingCode = null;
          deviceState.pairingCodeAt = 0;
          pairingInFlight = false;
          pendingPhone = null;
        }

        if (connection === 'close') {
          const rawCode = lastDisconnect?.error?.output?.statusCode ?? lastDisconnect?.error?.statusCode;
          const statusCode = typeof rawCode === 'number' ? rawCode : parseInt(rawCode, 10);
          console.log(`[BAILEYS] Koneksi tertutup (Status Code: ${statusCode})`);

          deviceState.connected = false;

          // 515 = restartRequired: Pairing berhasil di HP!
          // WhatsApp meminta reconnect instan agar auth keys baru aktif.
          if (statusCode === DisconnectReason.restartRequired || statusCode === 515) {
            console.log('[BAILEYS] >>> Status 515 (restartRequired) terdeteksi. Fast-handoff reconnect...');
            deviceState.status = 'connecting';
            pairingInFlight = false;
            pendingPhone = null;
            isStarting = false;
            setImmediate(() => {
              startBaileys();
            });
            return;
          }

          if (statusCode === DisconnectReason.loggedOut || statusCode === 401 || statusCode === 403) {
            console.log('[BAILEYS] Sesi telah logout / dicabut di HP. Mereset sesi...');
            deviceState.status = 'disconnected';
            deviceState.phone = null;
            deviceState.user = null;
            deviceState.pairingCode = null;
            pairingInFlight = false;
            pendingPhone = null;
            clearSession();
            isStarting = false;
          } else {
            // Reconnect bertahap untuk menjaga stabilitas
            deviceState.status = deviceState.pairingCode ? 'connecting' : 'disconnected';
            if (reconnectTimer) clearTimeout(reconnectTimer);
            reconnectTimer = setTimeout(() => {
              reconnectTimer = null;
              isStarting = false;
              startBaileys();
            }, 3000);
          }
        }
      }
    });

  } catch (err) {
    console.error('[BAILEYS] Gagal inisialisasi socket:', err);
  } finally {
    isStarting = false;
  }
}

// Inisialisasi awal saat server berjalan
startBaileys();

// HTTP REST API Server
const server = http.createServer((req, res) => {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, x-admin-token');

  if (req.method === 'OPTIONS') {
    res.writeHead(204);
    res.end();
    return;
  }

  const url = new URL(req.url, `http://${req.headers.host}`);
  const pathname = url.pathname;

  let bodyData = '';
  req.on('data', chunk => {
    bodyData += chunk;
  });

  req.on('end', async () => {
    let body = {};
    if (bodyData) {
      try {
        body = JSON.parse(bodyData);
      } catch (e) {}
    }

    const sendJson = (statusCode, data) => {
      res.writeHead(statusCode, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify(data));
    };

    console.log(`[WA-GATEWAY API] ${req.method} ${pathname}`);

    // Cek TTL pairing code
    if (deviceState.pairingCode && deviceState.pairingCodeAt && (Date.now() - deviceState.pairingCodeAt > PAIRING_TTL_MS)) {
      deviceState.pairingCode = null;
      deviceState.pairingCodeAt = 0;
      if (deviceState.status === 'connecting' && !deviceState.connected) {
        deviceState.status = 'disconnected';
      }
    }

    // 1. Health / Root
    if (pathname === '/' || pathname === '/api/health') {
      return sendJson(200, {
        status: true,
        service: 'WhatsApp Gateway Baileys Engine',
        version: '2.0.0',
        device_status: deviceState.status,
        connected: deviceState.connected,
      });
    }

    // 2. Device Status
    if (pathname === '/api/device/status' && req.method === 'GET') {
      if (deviceState.connected && deviceState.user && sock?.user?.id) {
        return sendJson(200, {
          status: 'connected',
          connected: true,
          phone: deviceState.phone,
          number: deviceState.phone,
          user: deviceState.user,
          paired_at: deviceState.pairedAt,
        });
      } else {
        const isPairing = !!deviceState.pairingCode;
        return sendJson(200, {
          status: isPairing ? 'connecting' : 'disconnected',
          connected: false,
          pairing_code: deviceState.pairingCode,
          phone: deviceState.phone,
          number: deviceState.phone,
          message: isPairing
            ? 'Menunggu input kode pairing di aplikasi WhatsApp HP...'
            : 'Perangkat belum tertaut ke WhatsApp',
        });
      }
    }

    // 3. Request Pairing Code (REAL BAILEYS / META)
    if (pathname === '/api/device/pairing-code' && req.method === 'POST') {
      const rawPhone = body.phone || body.number || '';
      const formattedPhone = formatPhoneNumber(rawPhone);

      if (!formattedPhone || formattedPhone.length < 8) {
        return sendJson(400, {
          status: false,
          message: 'Nomor WhatsApp tidak valid. Masukkan nomor HP Indonesia yang aktif (contoh: 088298674462).',
        });
      }

      pendingPhone = formattedPhone;

      try {
        if (deviceState.connected && sock?.user?.id) {
          return sendJson(200, {
            status: true,
            connected: true,
            phone: deviceState.phone,
            message: 'Perangkat WhatsApp sudah terhubung.',
          });
        }

        pairingInFlight = true;
        clearSession();
        await startBaileys();

        if (sock && typeof sock.waitForSocketOpen === 'function') {
          try {
            await sock.waitForSocketOpen();
          } catch (e) {
            console.warn('[BAILEYS] waitForSocketOpen notice:', e.message);
          }
        }

        await delay(1200);

        console.log(`[BAILEYS] Meminta kode pairing Meta untuk nomor: ${formattedPhone}...`);
        
        let customCode = body.custom_code || null;
        if (customCode) {
          customCode = String(customCode).toUpperCase().replace(/[^A-Z0-9]/g, '');
          if (customCode.length !== 8) customCode = null;
        }

        const rawCode = customCode
          ? await sock.requestPairingCode(formattedPhone, customCode)
          : await sock.requestPairingCode(formattedPhone);

        const formattedCode = (rawCode && rawCode.length === 8 && !rawCode.includes('-'))
          ? `${rawCode.slice(0, 4)}-${rawCode.slice(4)}`
          : rawCode;

        deviceState.pairingCode = formattedCode;
        deviceState.pairingCodeAt = Date.now();
        deviceState.connected = false;
        deviceState.status = 'connecting';

        console.log(`[BAILEYS] >>> KODE PAIRING RESMI DITERIMA: ${formattedCode}`);

        return sendJson(200, {
          status: true,
          code: formattedCode,
          phone: formattedPhone,
          message: 'Kode pairing berhasil dibuat. Buka WhatsApp di HP > Perangkat Tertaut > Tautkan dengan nomor telepon.',
          data: {
            code: formattedCode,
            phone: formattedPhone,
            expires_in: 180,
          },
        });
      } catch (err) {
        console.error('[BAILEYS] Gagal meminta kode pairing:', err);
        pairingInFlight = false;
        let msg = err.message || 'Gagal meminta kode pairing dari WhatsApp Meta';
        if (err.message?.includes('rate-overlimit') || err.data?.attrs?.text === 'rate-overlimit' || (err.message && String(err.message).includes('429'))) {
          msg = 'Nomor ini sedang terkena rate-limit dari server WhatsApp Meta. Tunggu jeda 10-15 menit untuk cooldown atau gunakan nomor lain.';
        }
        return sendJson(500, {
          status: false,
          message: msg,
        });
      }
    }

    // 4. Logout
    if (pathname === '/api/device/logout' && req.method === 'POST') {
      try {
        if (sock) {
          await sock.logout();
        }
      } catch (e) {}

      clearSession();
      deviceState.connected = false;
      deviceState.status = 'disconnected';
      deviceState.phone = null;
      deviceState.user = null;
      deviceState.pairingCode = null;
      deviceState.pairingCodeAt = 0;
      pairingInFlight = false;
      pendingPhone = null;

      setTimeout(() => {
        startBaileys();
      }, 1000);

      return sendJson(200, {
        status: true,
        connected: false,
        message: 'Koneksi WhatsApp Gateway berhasil diputuskan.',
      });
    }

    // 5. Send Message / OTP
    if ((pathname === '/api/send' || pathname === '/api/send-message') && req.method === 'POST') {
      const rawTarget = body.target || body.phone || body.number || body.to || '';
      const message = body.message || body.text || '';
      const formattedTarget = formatPhoneNumber(rawTarget);

      if (!deviceState.connected || !sock) {
        return sendJson(200, {
          status: false,
          message: 'WhatsApp Gateway belum terhubung ke perangkat mana pun. Silakan tautkan WhatsApp terlebih dahulu.',
        });
      }

      if (!formattedTarget || formattedTarget.length < 8) {
        return sendJson(400, {
          status: false,
          message: 'Nomor tujuan WhatsApp tidak valid.',
        });
      }

      try {
        const checkResults = await sock.onWhatsApp(formattedTarget);
        const onWa = Array.isArray(checkResults) ? checkResults.find(r => r.exists) : null;
        if (!onWa || !onWa.exists) {
          return sendJson(400, {
            status: false,
            message: `Nomor ${rawTarget} tidak terdaftar di WhatsApp aktif.`,
          });
        }

        const jid = onWa.jid || `${formattedTarget}@s.whatsapp.net`;
        const result = await sock.sendMessage(jid, { text: message });
        console.log(`[BAILEYS] Pesan terkirim ke ${jid} (ID: ${result?.key?.id}):\n${message}`);

        return sendJson(200, {
          status: true,
          target: formattedTarget,
          phone: formattedTarget,
          messageId: result?.key?.id || null,
          message: 'Pesan WhatsApp berhasil dikirim.',
        });
      } catch (err) {
        console.error('[BAILEYS] Gagal kirim pesan:', err);
        return sendJson(500, {
          status: false,
          message: 'Gagal kirim pesan WhatsApp: ' + err.message,
        });
      }
    }

    // Default 404
    sendJson(404, {
      status: false,
      message: `Endpoint ${pathname} tidak ditemukan pada WhatsApp Gateway Service.`,
    });
  });
});

server.listen(PORT, '127.0.0.1', () => {
  console.log(`=======================================================`);
  console.log(` WhatsApp Gateway (Real Baileys v2.0) running on:`);
  console.log(` http://127.0.0.1:${PORT}`);
  console.log(`=======================================================`);
});
