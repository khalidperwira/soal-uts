#!/usr/bin/env bash
# Deploy / update backend ujian. Jalankan dari folder /srv/ujian-api di VPS.
set -euo pipefail

# Nama project Compose (`ujian-api`) dipakai bersama semua salinan repo, jadi `up`
# dari folder dengan .env dev akan menimpa container produksi. Tolak kalau bukan produksi.
if ! grep -q '^APP_ENV=production$' .env 2>/dev/null; then
  echo "ERROR: .env di $(pwd) bukan produksi (APP_ENV!=production). Batal." >&2
  echo "Untuk update produksi dari clone repo, jalankan ../deploy-vps.sh" >&2
  exit 1
fi

echo "==> Menarik kode terbaru"
git pull --ff-only

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
curl -fsS https://ujian-uts.khalidperwira.com/up >/dev/null \
  && echo "OK: backend hidup" \
  || echo "PERINGATAN: /up tidak merespons, cek: docker compose logs -f app"
