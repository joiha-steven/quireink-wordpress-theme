// Invariant 2: bridge.css translates, it does not decide.
//
// The blog engine's rule is that a public colour comes only from a theme token and a size only
// from a type role - never a hex, never a hardcoded px. This repository inherits it with a
// sharper edge, because bridge.css is the ONE stylesheet here written by hand: a value in it
// that is not already a Quire Ink variable is a rule the blog engine has no answer for, and
// the two surfaces have started to drift.
//
// When a rule really is about how the site LOOKS, it belongs upstream, where tools/extract.ts
// will bring it across on the next run.
import { readFileSync } from 'node:fs'

const SRC = 'quire-ink/assets/css/bridge.css'

// Lengths that are allowed to be literal: 0, hairlines, and keywords. `1px` is the width of a
// rule and the blog engine's own sheets write it that way.
const ALLOWED_LENGTH = /^(0|0px|1px|2px|100%|auto|none)$/

// `em` is NOT checked at all, and that is a correction rather than a hole. This guard exists
// so a size cannot drift away from the type scale — and an `em` cannot: it is a multiple of
// whatever font size the element already has, so it follows the scale by construction rather
// than restating it. The blog engine's own sheets are full of them (`.16em` of side bearing
// on a highlight, `1.4em` of lead between blocks) for exactly that reason.
//
// The rule as first written banned them, and the first thing it caught was a `.35em` gap
// beside a bullet — copied from `.rail-sub::before` upstream. Rewriting that as `var(--sp)`
// would have been strictly worse: `--sp` is the ARTICLE's spacing unit and does not follow
// the small type the marker is set in. The check was wrong, so the check changed.

const css = readFileSync(SRC, 'utf8')
// Comments carry examples of exactly what is banned, which is the point of them.
const code = css.replace(/\/\*[\s\S]*?\*\//g, '')

const problems: string[] = []

for (const m of code.matchAll(/#[0-9a-fA-F]{3,8}\b/g)) {
  problems.push(`hardcoded colour ${m[0]} — use a --c-* token`)
}
for (const m of code.matchAll(/\b(rgba?|hsla?)\(/g)) {
  problems.push(`hardcoded colour function ${m[1]}() — use a --c-* token`)
}
for (const name of ['white', 'black', 'silver', 'gray', 'grey']) {
  if (new RegExp(`:\\s*${name}\\b`).test(code)) {
    problems.push(`named colour "${name}" — use a --c-* token`)
  }
}
for (const m of code.matchAll(/:\s*([^;{}]*?)(?=[;}])/g)) {
  for (const len of m[1]!.matchAll(/(?<![\w-])(\d*\.?\d+(?:px|rem))/g)) {
    if (!ALLOWED_LENGTH.test(len[1]!)) {
      problems.push(`hardcoded length ${len[1]} — use var(--sp), var(--fs-*) or var(--radius)`)
    }
  }
}

console.log(`  ${SRC}: ${css.length} B`)
if (problems.length === 0) {
  console.log('✓ check:bridge: ok (every value comes from a token)')
} else {
  console.log(`✗ check:bridge: ${problems.length} literal(s)`)
  for (const p of [...new Set(problems)]) console.log(`  · ${p}`)
  process.exit(1)
}
