// ========================================================
// Real Baileys WhatsApp Gateway Service (Fonte Standard)
// Port: 3000
// ========================================================

const http = require('http');
const path = require('path');
const fs = require('fs');
const {
  default: makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
  Browsers,
  delay,
  makeCacheableSignalKeyStore,
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
let pendingPhone = null;
let isStarting = false;

// CRITICAL:
// - connected HANYA boleh true jika connection: 'open' & sock.user.id valid.
// - Saat request pairing dibuat, status adalah 'connecting', connected adalah false.
let deviceState = {
  connected: false,
  status: 'disconnected',
  phone: null,
  user: null,
  pairingCode: null,
  pairedAt: null,
};

async function startBaileys() {
  if (isStarting) return;
  isStarting = true;

  try {
    const { state, saveCreds } = await useMultiFileAuthState(SESSION_DIR);
    const logger = pino({ level: 'silent' });

    sock = makeWASocket({
      auth: {
        creds: state.creds,
        keys: makeCacheableSignalKeyStore(state.keys, logger),
      },
      printQRInTerminal: false,
      logger: logger,
      browser: Browsers.ubuntu('Chrome'),
      connectTimeoutMs: 60000,
      defaultQueryTimeoutMs: 0,
      keepAliveIntervalMs: 10000,
      emitOwnEvents: true,
      fireInitQueries: true,
      syncFullHistory: false,
      markOnlineOnConnect: true,
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
      const { connection, lastDisconnect } = update;

      if (connection === 'connecting') {
        if (!deviceState.connected) {
          deviceState.status = deviceState.pairingCode ? 'connecting' : 'disconnected';
        }
        console.log('[BAILEYS] Menghubungkan ke server WhatsApp WebSocket...');
      }

      if (connection === 'open') {
        const rawId = sock.user?.id ? sock.user.id.split(':')[0] : (pendingPhone || 'Unknown');
        const userId = rawId.replace(/[^0-9]/g, '');
        console.log(`[BAILEYS] >>> WHATSAPP BERHASIL TERHUBUNG VALID (Meta Handshake Success)! Phone: ${userId}`);

        deviceState.connected = true;
        deviceState.status = 'connected';
        deviceState.phone = userId;
        deviceState.user = {
          id: sock.user?.id || `${userId}@s.whatsapp.net`,
          name: sock.user?.name || 'Bot Jurnal Kelas',
        };
        deviceState.pairedAt = new Date().toISOString();
        deviceState.pairingCode = null;
        pendingPhone = null;
      }

      if (connection === 'close') {
        const rawCode = lastDisconnect?.error?.output?.statusCode ?? lastDisconnect?.error?.statusCode;
        const statusCode = typeof rawCode === 'number' ? rawCode : parseInt(rawCode, 10);
        console.log(`[BAILEYS] Koneksi terputus (Status Code: ${statusCode})`);

        // Wajib set connected = false setiap kali socket close
        deviceState.connected = false;

        // 1. Meta Handshake Restart (Error 515 / restartRequired)
        // Meta menutup koneksi sesaat setelah pairing code dikonfirmasi di HP
        // agar client me-restart socket dengan auth keys baru yang tersimpan di SESSION_DIR.
        // DILARANG menghapus session_auth! Langsung sambungkan kembali.
        if (statusCode === DisconnectReason.restartRequired || statusCode === 515) {
          console.log('[BAILEYS] >>> Handshake Meta restart diperlukan (Error 515). Reconnect instan dengan credentials tersimpan...');
          deviceState.status = deviceState.pairingCode ? 'connecting' : 'disconnected';
          isStarting = false;
          await delay(1000);
          startBaileys();
        } else if (statusCode === DisconnectReason.loggedOut || statusCode === 401 || statusCode === 403) {
          console.log('[BAILEYS] Sesi dikeluarkan / Logged out oleh WhatsApp. Reset session...');
          deviceState.status = 'disconnected';
          deviceState.phone = null;
          deviceState.user = null;
          deviceState.pairingCode = null;
          clearSession();
          isStarting = false;
        } else {
          console.log(`[BAILEYS] Koneksi terputus (${statusCode}). Mencoba menghubungkan kembali dalam 3 detik...`);
          deviceState.status = deviceState.pairingCode ? 'connecting' : 'disconnected';
          setTimeout(() => {
            isStarting = false;
            startBaileys();
          }, 3000);
        }
      }
    });

  } catch (err) {
    console.error('[BAILEYS] Gagal inisialisasi socket:', err);
  } finally {
    isStarting = false;
  }
}

// Inisialisasi awal saat server dijalankan
startBaileys();

// HTTP API Server
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

    // 1. Device Status
    // STATUS 'connected' HANYA BOLEH bernilai true jika Baileys connection === 'open' & sock.user.id valid
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

    // 2. Request Pairing Code (REAL BAILEYS / META)
    if (pathname === '/api/device/pairing-code' && req.method === 'POST') {
      const rawPhone = body.phone || '';
      const formattedPhone = formatPhoneNumber(rawPhone);

      if (!formattedPhone || formattedPhone.length < 8) {
        return sendJson(400, {
          status: false,
          message: 'Nomor WhatsApp tidak valid. Masukkan nomor HP Indonesia yang aktif (contoh: 088298674462).',
        });
      }

      pendingPhone = formattedPhone;

      try {
        // Jika sudah tersambung valid, beritahukan user
        if (deviceState.connected && sock?.user?.id) {
          return sendJson(200, {
            status: true,
            connected: true,
            phone: deviceState.phone,
            message: 'Perangkat WhatsApp sudah terhubung.',
          });
        }

        // Jika socket belum dibuat atau ditutup, mulai socket baru
        if (!sock || sock.ws?.isClosed || sock.ws?.isClosing) {
          await startBaileys();
        }

        // Tunggu socket WebSocket Baileys terbuka dan siap kirim paket ke Meta
        if (sock && typeof sock.waitForSocketOpen === 'function') {
          try {
            await sock.waitForSocketOpen();
          } catch (e) {
            console.warn('[BAILEYS] waitForSocketOpen notice:', e.message);
          }
        }

        await delay(1500);

        console.log(`[BAILEYS] Meminta kode pairing resmi dari server WhatsApp Meta untuk nomor: ${formattedPhone}...`);
        const rawCode = await sock.requestPairingCode(formattedPhone);

        // Format kode 8-digit dengan hyphen jika belum ada: ABCD-EFGH
        const formattedCode = (rawCode && rawCode.length === 8 && !rawCode.includes('-'))
          ? `${rawCode.slice(0, 4)}-${rawCode.slice(4)}`
          : rawCode;

        // PENTING: Status connected WAJIB false! Tidak boleh 'halu' terhubung sebelum di-confirm di HP.
        deviceState.pairingCode = formattedCode;
        deviceState.connected = false;
        deviceState.status = 'connecting';

        console.log(`[BAILEYS] KODE PAIRING RESMI META DITERIMA: ${formattedCode}`);

        return sendJson(200, {
          status: true,
          code: formattedCode,
          phone: formattedPhone,
          message: 'Kode pairing WhatsApp resmi berhasil dibuat. Silakan masukkan di WhatsApp HP (Perangkat Tertaut).',
          data: {
            code: formattedCode,
            phone: formattedPhone,
            expires_in: 300,
          },
        });
      } catch (err) {
        console.error('[BAILEYS] Gagal meminta kode pairing:', err);
        return sendJson(500, {
          status: false,
          message: 'Gagal meminta kode pairing dari WhatsApp Meta: ' + (err.message || 'Timeout / Socket belum siap. Coba klik lagi dalam 5 detik.'),
        });
      }
    }

    // 3. Logout
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

      setTimeout(() => {
        startBaileys();
      }, 1000);

      return sendJson(200, {
        status: true,
        connected: false,
        message: 'Koneksi WhatsApp Gateway berhasil diputuskan.',
      });
    }

    // 4. Send Message / OTP
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
        const jid = `${formattedTarget}@s.whatsapp.net`;
        await sock.sendMessage(jid, { text: message });
        console.log(`[BAILEYS] Pesan terkirim ke ${jid}:\n${message}`);

        return sendJson(200, {
          status: true,
          target: formattedTarget,
          phone: formattedTarget,
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
      message: `Endpoint ${pathname} tidak ditemukan.`,
    });
  });
});

server.listen(PORT, '127.0.0.1', () => {
  console.log(`=======================================================`);
  console.log(` WhatsApp Gateway (Real Baileys) running on:`);
  console.log(` http://127.0.0.1:${PORT}`);
  console.log(`=======================================================`);
});
