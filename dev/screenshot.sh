#!/usr/bin/env bash
# Rebuild quire-ink/screenshot.png from a WordPress seeded for the purpose.
#
# WHY THIS IS A SCRIPT. The screenshot shipped for a long time as a render of the owner's own
# blog: their site name, their posts, their words. Honest, and it passes review, but a theme
# browsed by strangers should not open on one person's diary, and readme.txt now says the
# words in the picture were written for the picture. That sentence has to stay true, which
# means the picture has to be reproducible.
#
# WHAT IT DOES TO YOUR DATABASE. Everything. It exports, resets, seeds, renders and imports
# back, and the restore runs from a trap so that it happens even when the render fails. The
# dev database is disposable by design (dev/down.sh destroys the volume), but losing it in
# the middle of a session is still a waste of a seed run.
#
# WHY 1440 AND NOT 1200. WordPress.org wants exactly 1200x900. At a viewport of 1200 the left
# rail has already folded into the header's menu button, so a 1200-wide render shows the theme
# without the thing the theme is for. It renders at 1440 CSS pixels, twice over for the pixel
# density, and scales the 2880x2160 result down to 1200x900. The rail, the reading column and
# the timeline gutter are all in frame, and the downscale is what makes the type look printed
# rather than screenshotted.
set -euo pipefail

cd "$(dirname "$0")/.."

OUT="quire-ink/screenshot.png"
DUMP="/tmp/quireink-screenshot-restore.sql"
WIDE=".tmp/shots/screenshot-2880.png"

wpc() { docker compose -f dev/docker-compose.yml exec -T cli wp --path=/var/www/html "$@"; }

wpc core is-installed 2>/dev/null || { echo "no WordPress at :8099 — run dev/up.sh first" >&2; exit 1; }

echo "--- exporting the database first"
wpc db export "$DUMP" --quiet
restore() {
  echo "--- restoring the database"
  wpc db import "$DUMP" --quiet || echo "RESTORE FAILED — the dump is at $DUMP inside the cli container" >&2
}
trap restore EXIT

echo "--- a clean install"
wpc db reset --yes >/dev/null
wpc core install --url=http://localhost:8099 --title="Quire Ink" \
  --admin_user=admin --admin_password=admin --admin_email=dev@example.com --skip-email >/dev/null
wpc theme activate quire-ink >/dev/null
wpc post delete 1 2 3 --force >/dev/null 2>&1 || true

echo "--- content written for the picture"
CRAFT=$(wpc term create category Craft --porcelain)
NOTES=$(wpc term create category Notes --porcelain)

LEAD=$(wpc post create --post_type=post --post_status=publish \
  --post_title="The margin is where the reading happens" \
  --post_date="2026-08-18 09:12:00" --post_category="$CRAFT" \
  --tags_input="typography,reading" --porcelain \
  --post_content='<!-- wp:paragraph --><p>A page is not only the block of text in the middle of it. The space around a column tells a reader how long a line will be before they start it, and a line they can measure is a line they will finish. Widen the column by a third and the same paragraph takes longer to read, which is the opposite of what the extra room seemed to promise.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Where the number comes from</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Seventy characters is not a rule handed down from anywhere. It is roughly where the eye stops having to hunt for the start of the next line, and it happens to be close to what a printed book settles on after four hundred years of trying.</p><!-- /wp:paragraph -->')

wpc post create --post_type=post --post_status=publish \
  --post_title="Six palettes, and why none of them is blue on white" \
  --post_date="2026-07-30 16:40:00" --post_category="$CRAFT" \
  --tags_input="colour,design" --porcelain \
  --post_content='<!-- wp:paragraph --><p>Every palette here was set against a measurement rather than by eye. A grey that looked right on the screen it was chosen on turned out to be 2.26 to 1 against the page, which is invisible to anyone reading in daylight, and the fix was not a nicer grey but a number.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Light and dark are one decision</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>A palette that only works in one scheme is half a palette. Each of the six carries both, and the reader picks once.</p><!-- /wp:paragraph -->' >/dev/null

wpc post create --post_type=post --post_status=publish \
  --post_title="A contents rail that follows you down the page" \
  --post_date="2026-06-24 11:05:00" --post_category="$NOTES" \
  --tags_input="reading,navigation" --porcelain \
  --post_content='<!-- wp:paragraph --><p>The gutter beside an article is usually empty, and on a long piece it is the natural place for the shape of the article itself. The rail is built from the headings already in the post, so it needs no configuration and cannot fall out of step with the writing.</p><!-- /wp:paragraph -->' >/dev/null

# Sticky is what fills the rail's Featured block, and the bullet beside the headline.
wpc eval "stick_post($LEAD);" >/dev/null

MENU=$(wpc menu create "Rail" --porcelain)
wpc menu item add-term "$MENU" category "$CRAFT" >/dev/null
wpc menu item add-term "$MENU" category "$NOTES" >/dev/null
wpc menu location assign "$MENU" primary >/dev/null

echo "--- rendering"
SHOT_SCALE=2 tools/shot.sh "http://127.0.0.1:8099/" "$WIDE" 1440 1080
sips -z 900 1200 "$WIDE" --out "$OUT" >/dev/null

W=$(sips -g pixelWidth "$OUT" | awk '/pixelWidth/{print $2}')
H=$(sips -g pixelHeight "$OUT" | awk '/pixelHeight/{print $2}')
[ "$W" = "1200" ] && [ "$H" = "900" ] || { echo "wrong size: ${W}x${H}, wanted 1200x900" >&2; exit 1; }
printf '%s  %s  %sx%s\n' "$(du -h "$OUT" | cut -f1)" "$OUT" "$W" "$H"
