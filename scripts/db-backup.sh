#!/usr/bin/env bash
set -euo pipefail
umask 077
site_dir="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
backup_dir="$site_dir/.backups"
mkdir -p "$backup_dir"
chmod 700 "$backup_dir"
exec 9>"$backup_dir/.lock"
flock -n 9 || exit 0
cd "$site_dir"
dump="$backup_dir/duoduo-$(date -u +%Y%m%dT%H%M%SZ).sql.gz"
trap 'rm -f "$dump.tmp"' EXIT
docker compose exec -T mysql sh -c \
  'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump -uroot --single-transaction --quick --routines --triggers --events --set-gtid-purged=OFF --no-tablespaces --default-character-set=utf8mb4 "$MYSQL_DATABASE"' \
  </dev/null | gzip > "$dump.tmp"
gzip -t "$dump.tmp"
mv "$dump.tmp" "$dump"
echo "Database backup saved: $dump"
