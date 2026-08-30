#!/usr/bin/env bash
# Screenshot a page with headless Chrome.
#
# Two things this has to work around, both learned the hard way:
#
#   * Chrome 136+ IGNORES the automation flags when they would touch the real profile, so
#     every run gets a throwaway --user-data-dir. Without it the browser silently starts with
#     the developer's own profile and extensions, and the screenshot is of a page nobody else
#     will ever see.
#   * `--screenshot` writes the file and then sometimes does not exit. The watchdog below
#     waits for the file rather than for the process, which is the thing actually wanted.
#     macOS has no `timeout(1)`, so this is spelled out rather than delegated.
#
# Usage: shot.sh <url> <out.png> [width] [height]
set -euo pipefail

URL="$1"
OUT="$2"
W="${3:-1440}"
H="${4:-2400}"
WAIT="${SHOT_WAIT:-60}"

CHROME="${CHROME:-/Applications/Google Chrome.app/Contents/MacOS/Google Chrome}"
PROFILE="$(mktemp -d)"

mkdir -p "$(dirname "$OUT")"
rm -f "$OUT"

"$CHROME" \
  --headless=new \
  --disable-gpu \
  --no-sandbox \
  --hide-scrollbars \
  --force-device-scale-factor=2 \
  --user-data-dir="$PROFILE" \
  --window-size="${W},${H}" \
  --virtual-time-budget=20000 \
  --screenshot="$OUT" \
  "$URL" >/dev/null 2>&1 &
PID=$!

for _ in $(seq 1 "$WAIT"); do
  # Two ticks with the same size before believing it: the file appears the moment Chrome
  # opens it, and a screenshot read half-written is a corrupt PNG that looks like a
  # rendering bug.
  if [ -s "$OUT" ]; then
    a=$(wc -c < "$OUT")
    sleep 1
    b=$(wc -c < "$OUT")
    [ "$a" = "$b" ] && break
  fi
  kill -0 "$PID" 2>/dev/null || break
  sleep 1
done

kill "$PID" 2>/dev/null || true
wait "$PID" 2>/dev/null || true
rm -rf "$PROFILE"

test -s "$OUT" || { echo "no screenshot written: $OUT" >&2; exit 1; }
printf '%s  %s\n' "$(du -h "$OUT" | cut -f1)" "$OUT"
