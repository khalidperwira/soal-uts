#!/usr/bin/env bash
# Backup harian database ujian. Pasang di crontab root:
#   0 2 * * * /srv/scripts/backup-db.sh >> /var/log/backup-db.log 2>&1
set -euo pipefail

STACK_DIR=/srv/ujian-api
BACKUP_DIR=/srv/backups
RETENTION_DAYS=14

mkdir -p "$BACKUP_DIR"
cd "$STACK_DIR"

# shellcheck disable=SC1091
source .env

STAMP=$(date +%Y%m%d-%H%M)
OUT="$BACKUP_DIR/ujian-$STAMP.sql.gz"

docker compose exec -T db \
  mysqldump -u root -p"$DB_ROOT_PASSWORD" --single-transaction --routines "$DB_DATABASE" \
  | gzip > "$OUT"

echo "Backup selesai: $OUT ($(du -h "$OUT" | cut -f1))"

# Hapus backup lama
find "$BACKUP_DIR" -name 'ujian-*.sql.gz' -mtime +$RETENTION_DAYS -delete
