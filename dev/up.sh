#!/usr/bin/env bash
# Bring the viewer up and put a site in it.
#
# Idempotent on purpose: run it twice and the second run notices WordPress is already
# installed and only re-syncs the seed. That matters because the loop this exists for is
# "change the theme, look again", and a script that only works on an empty database makes
# people stop running it.
set -euo pipefail

cd "$(dirname "$0")"
URL="http://localhost:8099"

echo "==> containers"
docker compose up -d --wait

wp() { docker compose exec -T cli wp --path=/var/www/html "$@"; }

# The cli image races the web image: the volume is shared, but wp-config.php is written by
# the wordpress container's entrypoint and the cli container can start first.
echo "==> waiting for wp-config.php"
for _ in $(seq 1 60); do
  if docker compose exec -T cli test -f /var/www/html/wp-config.php; then break; fi
  sleep 1
done

if wp core is-installed 2>/dev/null; then
  echo "==> already installed"
else
  echo "==> installing"
  wp core install \
    --url="$URL" \
    --title="Quire Ink theme — local" \
    --admin_user=admin \
    --admin_password=admin \
    --admin_email=dev@example.com \
    --skip-email
fi

echo "==> theme"
wp theme activate quire-ink

echo "==> settings"
# Pretty permalinks, because the theme prints term links and a plain ?cat= URL is not what
# the sheet was measured against.
wp rewrite structure '/%postname%/' --hard
wp option update blogname "manhhung.me"
wp option update timezone_string "Asia/Ho_Chi_Minh"
wp option update date_format "j F, Y"

echo "==> seed"
./seed.sh

echo
echo "    $URL           the site"
echo "    $URL/wp-admin  admin / admin"
