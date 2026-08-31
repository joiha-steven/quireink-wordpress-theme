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
import { cjkLangCss } from '@/content/fonts'
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
