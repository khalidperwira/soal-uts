#!/usr/bin/env bash
# Deploy / update backend ujian. Jalankan dari folder /srv/ujian-api di VPS.
set -euo pipefail

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
