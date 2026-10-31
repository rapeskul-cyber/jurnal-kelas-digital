/**
 * WhatsApp Gateway Standalone Entrypoint
 * Menjalankan Baileys Gateway Server di port 3000
 * Terhubung langsung dengan Laravel Backend & Nuxt Frontend Jurnal Kelas Digital
 */

const path = require('path');
const gatewayScript = path.join(__dirname, 'wa-gateway', 'server.js');

console.log('[SYSTEM] Starting Jurnal Kelas Digital WhatsApp Gateway Service...');
require(gatewayScript);
