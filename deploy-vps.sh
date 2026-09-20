#!/usr/bin/env bash
# Update produksi: tarik kode terbaru dari git, salin app/ ke /srv/ujian-api,
# lalu build & restart dari sana. Jalankan dari clone repo (mis. ~/soal-uts).
#
# Kenapa bukan `app/deploy.sh` dari clone ini: nama project Compose sama
# (`ujian-api`), jadi menjalankan `docker compose up` dari clone akan menimpa
# container produksi dengan .env clone (dev/sqlite). Produksi selalu di-up dari
# $PROD_DIR, yang memegang .env dan docker-compose.yml produksi (ports loopback).
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROD_DIR="${PROD_DIR:-/srv/ujian-api}"
HEALTH_URL="${HEALTH_URL:-https://ujian-uts.khalidperwira.com/up}"

echo "==> Cek konfigurasi produksi di $PROD_DIR"
[[ -f "$PROD_DIR/.env" ]] || { echo "ERROR: $PROD_DIR/.env tidak ada"; exit 1; }
grep -q '^APP_ENV=production$' "$PROD_DIR/.env" \
  || { echo "ERROR: APP_ENV bukan production di $PROD_DIR/.env"; exit 1; }
grep -q '^DB_CONNECTION=mysql$' "$PROD_DIR/.env" \
  || { echo "ERROR: DB_CONNECTION bukan mysql di $PROD_DIR/.env"; exit 1; }

echo "==> Menarik kode terbaru"
git -C "$REPO_DIR" pull --ff-only

# .env, docker-compose.yml, storage, dan vendor milik produksi: jangan ditimpa.
echo "==> Menyalin app/ ke $PROD_DIR"
rsync -rlc \
  --exclude='/.env' --exclude='/.env.*' \
  --exclude='/docker-compose.yml' \
  --exclude='/vendor' --exclude='/node_modules' \
  --exclude='/storage' --exclude='/bootstrap/cache' \
  --exclude='/database/*.sqlite' --exclude='/.phpunit.result.cache' \
  "$REPO_DIR/app/" "$PROD_DIR/"

cd "$PROD_DIR"

echo "==> Build image"
docker compose build

echo "==> Menyalakan ulang service"
docker compose up -d --remove-orphans

echo "==> Membersihkan image lama"
docker image prune -f

echo "==> Status"
docker compose ps

echo "==> Uji endpoint kesehatan"
sleep 5
curl -fsS "$HEALTH_URL" >/dev/null \
  && echo "OK: backend hidup" \
  || echo "PERINGATAN: /up tidak merespons, cek: cd $PROD_DIR && docker compose logs -f app"
