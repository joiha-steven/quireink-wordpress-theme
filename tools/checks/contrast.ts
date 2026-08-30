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
// is deliberately NOT checked as a UI boundary; see docs/accessibility.md for the one place
// that is a real failure and why it is not answered here.
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
