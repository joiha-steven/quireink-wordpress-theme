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
<!-- wp:paragraph --><p>Seventy characters is not a rule handed down from anywhere. It is roughly where the eye stops having to hunt for the start of the next line, and it happens to be close to what a printed book settles on after four hundred years of trying.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>The number is a range rather than a point. Somewhere between sixty and eighty a line stops being work, and where it lands inside that range depends on the face, the size and how far apart the lines sit.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>What the gutter is for</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Holding the column to that width leaves room on both sides, and the room is not waste. One side takes the contents of the piece, the other takes its facts: when it was written, how long it runs, what it is filed under.</p><!-- /wp:paragraph -->
<!-- wp:quote --><blockquote class="wp-block-quote"><p>A margin is not empty space. It is the part of the page that tells you where the page ends.</p></blockquote><!-- /wp:quote -->
<!-- wp:heading --><h2>Reading on a phone</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>None of it survives contact with a narrow screen, and none of it should. Below the width that holds a rail beside a column, the gutters fold away, the contents move behind a button and the column takes the screen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>The part that is a taste</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Indented paragraphs, justified lines and hyphenation belong to print, and on a screen they are a preference rather than an improvement. They ship switched off, and the switch is one line in the Customizer.</p><!-- /wp:paragraph -->')

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

echo "--- the directory's screenshot"
SHOT_SCALE=2 tools/shot.sh "http://localhost:8099/" "$WIDE" 1440 1080
sips -z 900 1200 "$WIDE" --out "$OUT" >/dev/null

W=$(sips -g pixelWidth "$OUT" | awk '/pixelWidth/{print $2}')
H=$(sips -g pixelHeight "$OUT" | awk '/pixelHeight/{print $2}')
[ "$W" = "1200" ] && [ "$H" = "900" ] || { echo "wrong size: ${W}x${H}, wanted 1200x900" >&2; exit 1; }
printf '%s  %s  %sx%s\n' "$(du -h "$OUT" | cut -f1)" "$OUT" "$W" "$H"

# The pictures on the repository's front page, from the same seed, in the shape the blog
# engine's own README uses: composites rather than single frames, because the argument each
# one makes is a COMPARISON, and a caption under a pair says more than two captions under two
# pictures.
#
# Everything below renders against `localhost`, never `127.0.0.1`. WordPress writes its asset
# URLs against `siteurl`, so the other host loses every module script and every font to CORS
# and produces a page that looks almost right. tools/shot.sh refuses it now.
#
# Two of these need a CLICK - book mode opens from a button, and there is no URL that opens
# it. A page served from the WordPress origin can script its own same-origin iframes, so the
# composite is written into the webroot, rendered, and removed by the trap.
echo "--- the repository's pictures"
mkdir -p docs/shots .tmp/compose
ART=$(wpc post list --post_type=post --posts_per_page=1 --field=url)
ART_PATH="/${ART#*8099/}"

PAGE="_quireink-compose.html"
webroot_clean() { wpc_sh rm -f "/var/www/html/$PAGE"; }
wpc_sh() { docker compose -f dev/docker-compose.yml exec -T wordpress "$@"; }
trap 'webroot_clean; restore' EXIT

# Render an HTML file, from the WordPress origin when it needs to script its iframes.
compose() { # source-html, out, width, height, target-width
  docker compose -f dev/docker-compose.yml exec -T wordpress sh -c "cat > /var/www/html/$PAGE" < "$1"
  SHOT_SCALE=2 tools/shot.sh "http://localhost:8099/$PAGE" .tmp/compose/_raw.png "$3" "$4" >/dev/null
  sips -Z "$5" .tmp/compose/_raw.png --out "$2" >/dev/null
  printf '  %s  %s\n' "$(du -h "$2" | cut -f1)" "$2"
}

SHEET='html,body{margin:0}body{background:#ededed;display:flex;gap:20px;padding:20px;font:0/0 a}
.f{border:1px solid #d9d9d9;background:#fff;overflow:hidden;flex:none}iframe{border:0;display:block}'

# 1. The shape: the listing beside an article.
cat > .tmp/compose/demo.html <<HTML
<!doctype html><meta charset=utf-8><style>$SHEET
.f,iframe{width:1450px;height:1020px}</style>
<div class=f><iframe src="/"></iframe></div>
<div class=f><iframe src="$ART_PATH"></iframe></div>
HTML
compose .tmp/compose/demo.html docs/shots/demo.png 2960 1060 1200

# 2. The two reading treatments. Book mode is a dialog opened from a button; book typography
#    is a Customizer switch, so the right-hand frame carries a body class the setting also
#    sets, applied here rather than by re-rendering the whole site for one picture.
cat > .tmp/compose/reading.html <<HTML
<!doctype html><meta charset=utf-8><style>$SHEET
.f,iframe{width:1100px;height:1000px}</style>
<div class=f><iframe id=book></iframe></div>
<div class=f><iframe id=type></iframe></div>
<script>
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
const load=(el,u)=>new Promise(r=>{el.onload=()=>r();el.src=u});
(async()=>{
  localStorage.setItem('theme','light');
  const b=document.getElementById('book'), t=document.getElementById('type');
  await load(t,'$ART_PATH'); await sleep(400);
  t.contentDocument.body.classList.add('book-text');
  await load(b,'$ART_PATH'); await sleep(600);
  const btn=[...b.contentDocument.querySelectorAll('[data-book-open]')]
    .find(x=>x.getBoundingClientRect().width>0);
  if(btn) btn.click();
  await sleep(1200);
})();
</script>
HTML
compose .tmp/compose/reading.html docs/shots/demo-reading.png 2260 1040 1200

# 3. Six palettes. The palette is the OWNER's setting rather than the reader's, so each tile
#    is its own render; the reader's light/dark choice rides in localStorage, which the sheet
#    sets before the frame loads.

for pal in mono sepia forest ocean scifi amber; do
  wpc theme mod set quireink_palette "$pal" >/dev/null
  case "$pal" in ocean|scifi|amber) wpc theme mod set quireink_default_scheme dark >/dev/null ;;
                 *) wpc theme mod remove quireink_default_scheme >/dev/null 2>&1 || true ;; esac
  SHOT_SCALE=2 tools/shot.sh "$ART" ".tmp/compose/pal-$pal.png" 780 520 >/dev/null
done
wpc theme mod remove quireink_palette >/dev/null 2>&1 || true
wpc theme mod remove quireink_default_scheme >/dev/null 2>&1 || true

{
  echo '<!doctype html><meta charset=utf-8><style>html,body{margin:0}'
  echo 'body{background:#ededed;display:grid;grid-template-columns:repeat(3,1fr);gap:18px;padding:18px}'
  echo 'figure{margin:0}img{width:100%;display:block;border:1px solid #d9d9d9}'
  echo 'figcaption{font:500 15px/2.4 ui-monospace,SFMono-Regular,Menlo,monospace;color:#555;letter-spacing:.04em}</style>'
  for pal in mono sepia forest ocean scifi amber; do
    case "$pal" in ocean|scifi|amber) sch=dark ;; *) sch=light ;; esac
    echo "<figure><img src=\"file://$PWD/.tmp/compose/pal-$pal.png\"><figcaption>$pal &middot; $sch</figcaption></figure>"
  done
} > .tmp/compose/colour-sheet.html
SHOT_SCALE=2 tools/shot.sh "file://$PWD/.tmp/compose/colour-sheet.html" .tmp/compose/_raw.png 1240 648 >/dev/null
sips -Z 1200 .tmp/compose/_raw.png --out docs/shots/demo-colour.png >/dev/null
printf '  %s  %s\n' "$(du -h docs/shots/demo-colour.png | cut -f1)" docs/shots/demo-colour.png

# 4. Three phones. An iframe's viewport is its own size, so this is a real 390px layout -
#    which a headless window cannot be, because Chrome will not open one narrower than 500.
cat > .tmp/compose/mobile.html <<HTML
<!doctype html><meta charset=utf-8><style>$SHEET
body{gap:26px;padding:26px}.f{border-radius:14px}.f,iframe{width:390px;height:800px}</style>
<div class=f><iframe src="/"></iframe></div>
<div class=f><iframe src="$ART_PATH"></iframe></div>
<div class=f><iframe id=menu src="/"></iframe></div>
<script>
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
(async()=>{
  const m=document.getElementById('menu');
  await new Promise(r=>m.contentDocument.readyState==='complete'&&m.contentWindow.location.href!=='about:blank'?r():m.onload=r);
  await sleep(500);
  const t=m.contentDocument.querySelector('.rail-toggle,[data-rail-toggle],button[aria-controls]');
  if(t) t.click();
  await sleep(600);
})();
</script>
HTML
compose .tmp/compose/mobile.html docs/shots/demo-mobile.png 1300 860 1200

rm -f .tmp/compose/_raw.png
webroot_clean
