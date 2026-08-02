#!/bin/sh
# Scoreboard HQ - NAS install / update
# Run on the NAS (SSH or file station terminal).
set -e

REPO="https://github.com/sleevemcd/scoreboard-hq.git"
DIR="/volume1/docker/scoreboard"

if [ ! -d "$DIR/.git" ]; then
  echo "== Cloning for the first time =="
  git clone "$REPO" "$DIR"
else
  echo "== Pulling latest =="
  cd "$DIR" && git pull
fi

echo "== Fixing file permissions for nginx =="
chmod -R a+rX "$DIR"
chown -R root:root "$DIR" 2>/dev/null || true

echo "== Starting container =="
cd "$DIR" && docker compose up -d --build

echo "Done. Open http://<nas-ip>:1212/scoreboard.html"