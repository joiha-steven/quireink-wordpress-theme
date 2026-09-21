/**
 * The owner's choices, once per possible answer, as a PHP map.
 *
 * Split out of `extract.ts` when that file hit the 400-line ceiling, and the seam is a real
 * one: everything left there moves BYTES across - sheets, faces, bundles - and everything
 * here runs the blog engine's emitters once per answer an owner could give. Same seam the
 * theme cut `inc/appearance-css.php` on, and the same one the engine cut
 * `content/settings-css.ts` on.
 */
import { DEFAULT_SETTINGS } from '@/content/settings'
import { themesToCss, fontPresetCss, chromeFontCss, THEME_PRESETS } from '@/content/themes'
import { FONT_PRESETS, CHROME_FONTS, SCHEMES } from '@/content/themes'
import { cjkLangCss, fontPreloadHrefs } from '@/content/fonts'
import { typographyToCss } from '@/content/settings'
import { pageStyles } from '@/web/layout'
import { BOOK_CSS } from '@/web/book.css'

const s = DEFAULT_SETTINGS

// The palette and the typeface are SETTINGS in the blog engine and were baked constants here
// until an owner asked where to change them. They cannot live in the static sheet: which of
// the six is the default, and which typeface the words are set in, are decisions a site makes
// after it installs the theme.
//
// So the emitters are run once per possible answer and the results written out as a PHP map,
// which `quireink_appearance_css()` picks from at request time. It is generated, so the
// no-hand-copying rule still holds: every string below is what the blog engine produces for
// that combination, not a value anybody read off a screen.
//
// The whole file is ~14 KB of PHP that is never sent to a browser; what reaches the page is
// one block of a few hundred bytes, and nothing at all when the owner has changed nothing.
const phpString = (v: string) => `'${v.replaceAll('\\', '\\\\').replaceAll("'", "\\'")}'`

const paletteEntries = THEME_PRESETS.map((preset) => {
  const perScheme = SCHEMES.map((scheme) => {
    // `enabled: []` keeps this to the :root/.dark pair plus the first-paint rule for that
    // scheme — the `[data-palette]` blocks the switcher needs are already in the static
    // sheet and repeating them per palette would be six copies of the same six rules.
    const css = themesToCss({ [preset.id]: preset.theme }, preset.id, [], scheme)
    return `\t\t'${scheme}' => ${phpString(css)},`
  }).join('\n')
  return `\t'${preset.id}' => array(\n${perScheme}\n\t),`
}).join('\n')

const fontEntries = FONT_PRESETS.map((f) =>
  `\t'${f.id}' => ${phpString(fontPresetCss(f.id) + cjkLangCss(f.id))},`).join('\n')

const chromeEntries = CHROME_FONTS.map((c) =>
  `\t'${c.id}' => ${phpString(chromeFontCss(c.id))},`).join('\n')

/*
 * WHAT THE STATIC SHEET ALREADY SAYS, so that `inc/appearance-css.php` can stop guessing.
 *
 * That file emits a setting's CSS only when the setting differs from the default, because at
 * the default the generated sheet already carries it. The defaults it compared against were
 * TYPED - `'jetbrains-mono' !== $chrome` - and they were the THEME's defaults, not the blog
 * engine's, which is a different thing and drifted on 2026-09-13 when the engine's own
 * furniture face moved to Inter.
 *
 * What that cost, measured on a default install: `--font-sans` was Inter while the theme said
 * JetBrains Mono. The enumerated `html[data-chrome-font=...]` rules kept the body, the meta
 * line and the rails monospace, so the page looked right - and every element that reads
 * `var(--font-sans)` directly did not. `.code-copy`, the key on a code block, fetched 32.5 KB
 * of Inter to set the word "Copy".
 *
 * These are the engine's values at extract time, so the comparison cannot drift again: the
 * day upstream moves a default, this moves with it and the theme emits the declaration that
 * holds its own default in place.
 */
const defaultEntries = [
  ['palette', THEME_PRESETS[0]!.id],
  ['font', s.fontPreset],
  ['chrome', s.chromeFont],
].map(([k, v]) => `\t\t'${k}' => ${phpString(String(v))},`).join('\n')

/*
 * THE FONT PRELOAD, PROBED RATHER THAN READ.
 *
 * `fontPreloadHrefs()` in `src/content/fonts.ts` is the blog engine's whole rule for which
 * faces earn a `<link rel=preload>`, and it is a rule with a price on it: the file says a
 * default install once preloaded 33 KB of a chrome face it barely paints a glyph in, and that
 * mistake cost 160ms of LCP. So it is CALLED here rather than reimplemented - this theme was
 * shipping no preload at all, which is the other way to get the same answer wrong.
 *
 * It takes a language, and its answer depends on one: a Vietnamese site needs a second subset
 * and a Japanese one needs nothing, because none of the bundled faces carries a Han glyph and
 * a preload of latin is bandwidth taken from a page that will paint in a system face.
 *
 * WHICH LANGUAGES THOSE ARE IS NOT TYPED HERE. Every two-letter code is put through the
 * function and the answers are grouped: whatever most codes get becomes `*`, and a code that
 * gets something else is named. The list of exceptions is therefore a MEASUREMENT of the
 * engine's rule rather than a copy of it, and the day upstream adds Thai or drops Russian,
 * the next extract says so without anyone remembering to look.
 */
const TWO_LETTER = Array.from({ length: 26 }, (_, i) =>
  String.fromCharCode(97 + i)).flatMap((a) =>
  Array.from({ length: 26 }, (_, i) => a + String.fromCharCode(97 + i)))

const preloadEntries = FONT_PRESETS.flatMap((f) => CHROME_FONTS.map((c) => {
  // `false` is hasCustomFont: an uploaded face is a blog-engine setting with no counterpart
  // in a theme. 'plain' is the look: the engine's newspaper look sets its headline in a third
  // face, and this theme has no such look (`data-look` is `code` or `plain`).
  const answer = (lang: string) => fontPreloadHrefs(f.id, lang, false, c.id, 'plain')
    .map((href) => href.replace('/fonts/', ''))

  const tally = new Map<string, number>()
  for (const lang of TWO_LETTER) {
    const key = JSON.stringify(answer(lang))
    tally.set(key, (tally.get(key) ?? 0) + 1)
  }
  const common = [...tally].sort((a, b) => b[1] - a[1])[0][0]
  const named = TWO_LETTER
    .filter((lang) => JSON.stringify(answer(lang)) !== common)
    .map((lang) => `\t\t'${lang}' => array(${answer(lang).map(phpString).join(', ')}),`)

  const rows = [`\t\t'*' => array(${(JSON.parse(common) as string[]).map(phpString).join(', ')}),`, ...named]
  return `\t'${f.id}|${c.id}' => array(\n${rows.join('\n')}\n\t),`
})).join('\n')

// The SITE-WIDE picture frame, taken out of the blog engine by DIFFERENCE.
//
// `figureCss()` in `src/web/layout.ts` is not exported, so it cannot be called - but
// `pageStyles()` is, and `figureCss()` returns the empty string at the default frame. So a
// sheet built with a frame set differs from a sheet built without one by exactly the block
// this needs, and taking the difference is a way of calling a private function through the
// public one. No value is retyped, and the day the engine changes what a frame is, this
// changes with it.
//
// Six combinations rather than eight: `none` says nothing whichever way `ink` is set.
const FRAMES = ['thin', 'medium', 'thick'] as const
const figureEntries = FRAMES.flatMap((frame) => [false, true].map((ink) => {
  const before = pageStyles({ ...s, figure: { frame: 'none', ink: false } }).split('\n')
  const after = pageStyles({ ...s, figure: { frame, ink } }).split('\n')
  const extra = after.filter((line) => !before.includes(line))
  if (extra.length !== 1) {
    throw new Error(
      `Setting figure.frame=${frame} ink=${ink} changed ${extra.length} lines of pageStyles(),\n`
      + '  not the one that figureCss() emits. Read src/web/layout.ts before touching this:\n'
      + '  the difference trick only holds while figureCss is the only part that reacts.\n'
      + extra.map((l) => '    ' + l.slice(0, 120)).join('\n'),
    )
  }
  return `\t'${frame}${ink ? '-ink' : ''}' => ${phpString(extra[0]!)},`
})).join('\n')

// The drop cap, lifted off the blog engine's own rule rather than typed out.
//
// Core's Button-block-era drop cap is `font-size:8.4em;font-weight:100;line-height:.68`,
// which at this theme's 18.4px paragraph is a 151px letter spanning 3.85 body lines, drawn
// in a weight Literata does not ship. The engine sets its own at 3.1em, weight 600, in the
// heading's ink - a third the size and a different animal. Measured side by side, core's is
// not a taste this theme happens not to share; it is a different typeface at a different
// scale sitting in the middle of a reading column.
//
// So the declarations come out of BOOK_CSS, where the engine states them once for book mode,
// and are re-addressed at the class Gutenberg puts on the paragraph. Not copied: if the
// engine restyles its drop cap, the next extract moves this with it, and `check:generated`
// goes red until it does.
const dropCapRule = /\.book-flow\.prose > p:first-child::first-letter\{([^}]*)\}/.exec(BOOK_CSS)
if (!dropCapRule) {
  throw new Error(
    'No `.book-flow.prose > p:first-child::first-letter` rule in BOOK_CSS.\n'
    + '  The blog engine moved its drop cap. Find where it states it now and point this at it,\n'
    + '  rather than writing the numbers out here - that is the one thing this file may not do.',
  )
}
const dropCap = dropCapRule[1]!.replace(/\s+/g, ' ').trim()

export const APPEARANCE_PHP = `<?php
/**
 * GENERATED by tools/extract.ts — do not edit. Run \`bun tools/extract.ts\`.
 *
 * Every palette and every typeface the blog engine offers, already turned into CSS by the
 * blog engine's own emitters. \`quireink_appearance_css()\` in inc/customizer.php picks the
 * one the owner chose and prints it after the stylesheets, exactly where Quire Ink inlines
 * its own settings block.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The blog engine's drop cap, addressed at the class the block editor writes.
 *
 * Gutenberg offers a drop cap on any paragraph and styles it 8.4em at weight 100, which is
 * three times this theme's own and a weight its typefaces do not carry. These are the
 * engine's declarations, taken from its book-mode sheet at build time.
 *
 * @return string
 */
function quireink_dropcap_css() {
	return '.prose .has-drop-cap:not(:focus)::first-letter{${dropCap}}';
}

/**
 * What the generated sheet already carries, as the blog engine set it at extract time.
 *
 * \`quireink_appearance_css()\` prints a setting's declarations only when the setting differs
 * from one of these. They are the ENGINE's defaults, not the theme's: the theme's furniture
 * face is JetBrains Mono and the engine's is Inter, and comparing against the wrong one left
 * \`--font-sans\` at the engine's value on every default install.
 *
 * @return array<string,string>
 */
function quireink_engine_defaults() {
	return array(
${defaultEntries}
	);
}

/**
 * Palette id => first-paint scheme => the :root and .dark declarations.
 *
 * @return array<string,array<string,string>>
 */
function quireink_palette_css() {
	return array(
${paletteEntries}
	);
}

/**
 * Reading typeface id => the family, the type scale tuned for it, and its CJK tail.
 *
 * @return array<string,string>
 */
function quireink_font_css() {
	return array(
${fontEntries}
	);
}

/**
 * Chrome typeface id => the family for the furniture.
 *
 * @return array<string,string>
 */
function quireink_chrome_css() {
	return array(
${chromeEntries}
	);
}

/**
 * Reading face and furniture face => the woff2 files that earn a \`<link rel="preload">\`.
 *
 * Keyed \`<reading>|<furniture>\`, then by the site's language. \`*\` is what a language gets
 * unless it is named; a named one is an exception the blog engine's own rule makes - a second
 * subset where the faces carry the accents, nothing at all where they carry no glyph the page
 * will paint in.
 *
 * @return array<string,array<string,array<int,string>>>
 */
function quireink_font_preload() {
	return array(
${preloadEntries}
	);
}

/**
 * Frame id => the four \`--fig-default-*\` variables \`figure img\` reads when a picture names
 * no frame of its own. \`none\` is absent because it emits nothing.
 *
 * @return array<string,string>
 */
function quireink_figure_css() {
	return array(
${figureEntries}
	);
}
`

/** What the extractor prints, so the shape of this file is visible from its caller. */
export const APPEARANCE_COUNTS = {
  palettes: THEME_PRESETS.length,
  schemes: SCHEMES.length,
  fonts: FONT_PRESETS.length,
  chrome: CHROME_FONTS.length,
  frames: FRAMES.length * 2,
}
