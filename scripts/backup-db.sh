#!/usr/bin/env bash
# JejakBahari database backup script.
# Usage: ./backup-db.sh [backup_dir]
#
# Defaults: reads DB_* env vars or backend/.env; writes compressed
# pg_dump to ./backups (or given dir); retains 7 daily backups.
#
# Schedule via cron:
#   0 3 * * * /var/www/jejakbahari/scripts/backup-db.sh /var/backups/jejakbahari >> /var/log/jejakbahari/backup.log 2>&1

set -euo pipefail

BACKUP_DIR="${1:-./backups}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-7}"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"

# Load backend/.env if present and DB vars not already set
ENV_FILE="$(dirname "$0")/../backend/.env"
if [ -f "$ENV_FILE" ] && [ -z "${DB_DATABASE:-}" ]; then
  set -a
  # shellcheck disable=SC1090
  . "$ENV_FILE"
  set +a
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
DB_NAME="${DB_DATABASE:-jejakbahari}"
DB_USER="${DB_USERNAME:-jejakbahari}"

if [ -z "${DB_PASSWORD:-}" ]; then
  echo "ERROR: DB_PASSWORD not set (export it or add to backend/.env)" >&2
  exit 1
fi

mkdir -p "$BACKUP_DIR"

DUMP_FILE="$BACKUP_DIR/jejakbahari-${TIMESTAMP}.sql.gz"

echo "[$(date -Is)] Starting backup of $DB_NAME@$DB_HOST:$DB_PORT"

export PGPASSWORD="$DB_PASSWORD"
pg_dump \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --username="$DB_USER" \
  --dbname="$DB_NAME" \
  --format=plain \
  --no-owner \
  --no-privileges \
  | gzip -9 > "$DUMP_FILE"
unset PGPASSWORD

SIZE=$(du -h "$DUMP_FILE" | cut -f1)
echo "[$(date -Is)] Backup written: $DUMP_FILE ($SIZE)"

# Retention: delete backups older than RETENTION_DAYS
DELETED=$(find "$BACKUP_DIR" -name 'jejakbahari-*.sql.gz' -mtime +"$RETENTION_DAYS" -delete -print | wc -l)
echo "[$(date -Is)] Retention: deleted $DELETED backups older than ${RETENTION_DAYS}d"

echo "[$(date -Is)] Backup complete."
