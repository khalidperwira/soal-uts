#!/usr/bin/env bash
# Jalankan aplikasi Laravel (soal-uts) di lokal.
# Menyiapkan .env/APP_KEY/dependency kalau perlu, migrasi + seed database,
# lalu start dev server (artisan serve + queue:listen + pail + vite) via `composer run dev`.
set -euo pipefail
cd "$(dirname "$0")"

if [ ! -f .env ]; then
  echo "==> .env tidak ditemukan, menyalin dari .env.example"
  cp .env.example .env
fi

if [ ! -d vendor ]; then
  echo "==> Menginstal dependency Composer"
  composer install
fi

if ! grep -q '^APP_KEY=base64' .env; then
  echo "==> APP_KEY kosong, generate baru"
  php artisan key:generate
fi

if [ ! -d node_modules ]; then
  echo "==> Menginstal dependency npm"
  npm install
fi

DB_HOST=$(grep -m1 '^DB_HOST=' .env | cut -d= -f2 | tr -d '\r')
DB_PORT=$(grep -m1 '^DB_PORT=' .env | cut -d= -f2 | tr -d '\r')
if [ -n "${DB_HOST:-}" ] && [ -n "${DB_PORT:-}" ]; then
  if ! php -r "exit(@fsockopen('${DB_HOST}', (int) '${DB_PORT}', \$errno, \$errstr, 2) ? 0 : 1);" 2>/dev/null; then
    echo "PERINGATAN: tidak bisa konek ke database ${DB_HOST}:${DB_PORT}."
    echo "            Pastikan MySQL sudah jalan (mis. start service MySQL di Laragon)."
  fi
fi

echo "==> Menjalankan migrasi database"
if php artisan migrate; then
  echo "==> Menjalankan seeder (data dummy)"
  php artisan db:seed || echo "PERINGATAN: seeding gagal."
else
  echo "PERINGATAN: migrasi gagal, cek koneksi/konfigurasi database di .env. Seeder dilewati."
fi

echo "==> Menjalankan dev server (artisan serve + queue:listen + pail + vite)"
echo "    Tekan Ctrl+C untuk berhenti."
composer run dev
