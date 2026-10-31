#!/bin/bash
fuser -k -9 3010/tcp || true
pkill -9 -f "output/server/index.mjs" || true
sleep 1
cd /home/agentuser/jurnal-kelas-digital/frontend
HOST=127.0.0.1 PORT=3010 nohup node .output/server/index.mjs > nuxt.log 2>&1 &
sleep 2
echo "Nuxt running with PID:"
pgrep -f "output/server/index.mjs" || echo "Not running!"
