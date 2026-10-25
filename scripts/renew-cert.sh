#!/usr/bin/env bash
set -euo pipefail
site_dir="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
exec 9>/run/lock/duoduo-renew-cert.lock
flock -n 9 || exit 0
cd "$site_dir"
docker run --rm --memory=192m --memory-swap=384m \
  -v "$site_dir/html:/var/www/html" \
  -v "$site_dir/ssl/certbot:/etc/letsencrypt" \
  certbot/certbot:v5.8.0 renew --webroot -w /var/www/html --quiet "$@"
docker compose exec -T nginx nginx -t </dev/null
docker compose exec -T nginx nginx -s reload </dev/null
