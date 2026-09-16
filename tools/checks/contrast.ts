// Every palette clears WCAG AA against its own background.
//
// This started as a one-off audit and is a guard because the palettes are GENERATED: the blog
// engine owns those seven colours, the engine moves, and a re-extract can walk a ratio under
// the line with nothing on screen looking any different. A number nobody re-measures is a
// number that used to be true.
//
// It reads the generated PHP rather than the tokens sheet, because that file carries all six
// palettes in both schemes - the sheet only carries the one the owner picked.
//
// The floors are WCAG 2.1 AA: 4.5:1 for text, 3:1 for non-text that carries meaning. `--c-rule`
// is deliberately NOT checked as a UI boundary: it draws hairlines between cards, which are
// decorative, and it is 1.26 to 1.35:1 by design.
//
// ⚠️ A CONTROL'S EDGE IS CHECKED, AND IT IS THE REASON THIS THEME MAY SAY `accessibility-ready`.
// Until 2026-09-16 a text input's only boundary WAS `--c-rule` and SC 1.4.11 wants 3:1, which
// is the one criterion that kept the tag off. The blog engine answered it with a token of its
// own, `--c-field-edge`, and the tag went on. A tag is a claim a reviewer checks with a colour
// picker in a minute, so it is held here by arithmetic rather than by anyone's memory: the
// engine computes the mix, this recomputes it from what the theme actually SHIPS.
import { readFileSync } from 'node:fs'

const SRC = 'quire-ink/inc/generated-appearance.php'

const FLOORS: Array<[string, number]> = [
  ['--c-text', 4.5],
  ['--c-heading', 4.5],
  ['--c-meta', 4.5],
  ['--c-link', 4.5],
  ['--c-accent', 3],
]

function luminance(hex: string): number {
  const channel = (i: number): number => {
    const v = parseInt(hex.slice(i, i + 2), 16) / 255
    return v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4
  }
  return 0.2126 * channel(1) + 0.7152 * channel(3) + 0.0722 * channel(5)
}

function contrast(a: string, b: string): number {
  const [x, y] = [luminance(a), luminance(b)]
  const [hi, lo] = x! > y! ? [x!, y!] : [y!, x!]
  return (hi + 0.05) / (lo + 0.05)
}

const php = readFileSync(SRC, 'utf8')
const table = php.slice(php.indexOf('function quireink_palette_css'))
const palettes = table.split(/\n\t'([a-z-]+)' => array\(/)

const problems: string[] = []
let worst = { ratio: Infinity, where: '' }
let checked = 0

for (let i = 1; i < palettes.length; i += 2) {
  const id = palettes[i]!
  // The 'light' entry carries BOTH halves: `:root{...}` is the light scheme and `.dark{...}`
  // the dark one. The other two entries are the same colours with a different first paint.
  const both = /'light' => '([^']*)'/.exec(palettes[i + 1] ?? '')?.[1]
  if (both === undefined) { problems.push(`${id}: no 'light' entry to read`); continue }

  for (const [scheme, pattern] of [['light', /^:root\{([^}]*)\}/], ['dark', /\.dark\{([^}]*)\}/]] as const) {
    const block = pattern.exec(both)?.[1]
    if (block === undefined) continue
    const vars = new Map([...block.matchAll(/(--c-[a-z]+):(#[0-9a-f]{6})/g)].map((m) => [m[1]!, m[2]!]))
    const bg = vars.get('--c-bg')
    if (bg === undefined) { problems.push(`${id}/${scheme}: no --c-bg`); continue }

        // `--c-field-edge` ships as a `color-mix()` rather than a hex, because it follows the
    // palette. In sRGB that is a plain per-channel interpolation, so it can be computed here
    // exactly as a browser computes it, from the two hexes sitting in the same block.
    const mix = /--c-field-edge:color-mix\(in srgb, *var\(--c-text\) *(\d+)%, *var\(--c-bg\)\)/.exec(block)
    const text = vars.get('--c-text')
    if (mix === undefined || text === undefined) {
      problems.push(`${id}/${scheme}: no --c-field-edge to check, and the tag claims one`)
    } else {
      const p = Number(mix[1]) / 100
      const edge = '#' + [0, 2, 4].map((k) => {
        const a = parseInt(text.slice(k + 1, k + 3), 16)
        const b = parseInt(bg.slice(k + 1, k + 3), 16)
        return Math.round(a * p + b * (1 - p)).toString(16).padStart(2, '0')
      }).join('')
      const ratio = contrast(edge, bg)
      checked++
      if (ratio < worst.ratio) worst = { ratio, where: `${id}/${scheme} --c-field-edge` }
      if (ratio < 3) {
        problems.push(
          `${id}/${scheme} --c-field-edge ${edge} on ${bg} = ${ratio.toFixed(2)}:1, needs 3:1.\n`
          + '    This is the criterion accessibility-ready rests on. Answer it in the blog\n'
          + '    engine and re-extract; do not patch it in bridge.css.',
        )
      }
    }

    for (const [role, floor] of FLOORS) {
      const colour = vars.get(role)
      if (colour === undefined) { problems.push(`${id}/${scheme}: no ${role}`); continue }
      const ratio = contrast(colour, bg)
      checked++
      if (ratio < worst.ratio) worst = { ratio, where: `${id}/${scheme} ${role}` }
      if (ratio < floor) {
        problems.push(`${id}/${scheme} ${role} ${colour} on ${bg} = ${ratio.toFixed(2)}:1, needs ${floor}:1`)
      }
    }
  }
}

// And the ratio is worth nothing if a control stops ASKING for the token. A re-extract that
// pointed a field's border back at `--c-rule` would leave every number above still true and
// the tag still declared, with the boundary back at 1.26:1 on the screen. So the sheets are
// read for it: anything a reader types into takes `--c-field-edge` or this says which one did
// not.
const TYPED_INTO = /([^{};`\n]*(?:input|textarea|select)[^{};`\n]*)\{([^}]*border:[^}]*)\}/g
for (const sheet of ['quireink-base.css', 'quireink-look-code.css']) {
  const css = readFileSync(`quire-ink/assets/css/${sheet}`, 'utf8')
  for (const m of css.matchAll(TYPED_INTO)) {
    const [, sel, body] = m
    if (!body!.includes('var(--c-rule)')) continue
    if (!/border(-[a-z]+)?:[^;]*var\(--c-rule\)/.test(body!)) continue
    problems.push(
      `${sheet}: \`${sel!.trim().slice(0, 56)}\` borders on --c-rule, not --c-field-edge.\n`
      + '    That is 1.26:1 and the theme declares accessibility-ready.',
    )
  }
}

if (checked === 0) problems.push('no palettes were read at all — the generated file changed shape')

console.log(`  ${checked} colour(s) against their own background in ${SRC}`)
if (problems.length === 0) {
  console.log(`✓ check:contrast: ok (tightest ${worst.ratio.toFixed(2)}:1, ${worst.where})`)
} else {
  console.log(`✗ check:contrast: ${problems.length} problem(s)`)
  for (const p of problems) console.log(`  · ${p}`)
  console.log('  the palettes come from the blog engine — this is a conversation upstream, not a patch here')
  process.exit(1)
}
