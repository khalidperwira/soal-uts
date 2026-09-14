#!/bin/sh
set -e

# Volume storage bisa kosong saat pertama kali dibuat -> siapkan strukturnya
mkdir -p storage/framework/cache \
         storage/framework/sessions \
         storage/framework/views \
         storage/app/public \
         storage/logs \
         bootstrap/cache

# Tunggu database siap (maks ~60 detik)
i=0
until php -r 'exit(@fsockopen(getenv("DB_HOST"), (int)getenv("DB_PORT")) ? 0 : 1);' 2>/dev/null; do
  i=$((i+1))
  [ "$i" -ge 60 ] && echo "Database tidak merespons, lanjut saja..." && break
  echo "Menunggu database... ($i)"
  sleep 1
done

# Migrasi otomatis (aman: hanya menjalankan migrasi yang belum jalan)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force --no-interaction || echo "Migrasi gagal, cek log."
fi

# Cache konfigurasi supaya request lebih cepat
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true

exec "$@"
