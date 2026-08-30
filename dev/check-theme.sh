#!/usr/bin/env bash
# Run the gate WordPress.org actually puts a submission through.
#
# Theme Check only ever shipped an admin screen, and remembering to open it before a
# submission is not a process. The plugin also ships a WP-CLI command, so it runs here — on
# the same bind-mounted theme the browser is showing, which means a finding can be fixed and
# re-checked without a build, an upload or a zip.
#
# It is NOT part of `bun run check:all`: that suite is static, runs in seconds and needs
# nothing running. This needs Docker, a database and a WordPress install. Run it before a
# release, not on every save.
set -euo pipefail

cd "$(dirname "$0")"

if ! docker compose ps --status running --quiet cli >/dev/null 2>&1; then
  echo "the local WordPress is not up: dev/up.sh" >&2
  exit 1
fi

if ! docker compose exec -T cli wp --path=/var/www/html plugin is-active theme-check >/dev/null 2>&1; then
  echo "==> installing theme-check"
  docker compose exec -T cli wp --path=/var/www/html plugin install theme-check --activate
fi

echo "==> theme-check"
out="$(docker compose exec -T cli wp --path=/var/www/html theme-check run quire-ink 2>&1 || true)"

echo "$out" | grep -E '^(REQUIRED|WARNING|RECOMMENDED)' | sort || true

req=$(echo "$out" | grep -c '^REQUIRED' || true)
warn=$(echo "$out" | grep -c '^WARNING' || true)
rec=$(echo "$out" | grep -c '^RECOMMENDED' || true)

echo
echo "$req REQUIRED · $warn WARNING · $rec RECOMMENDED"

# RECOMMENDED does not fail the build. The four that remain are answered in
# docs/decisions/0007-four-recommendations-declined.md; a fifth appearing is worth reading,
# not worth blocking on.
if [ "$req" -gt 0 ] || [ "$warn" -gt 0 ]; then
  echo "not submittable" >&2
  exit 1
fi
echo "no REQUIRED or WARNING findings"
