// Holds the 400-line rule from CLAUDE.md. Prose rots, a red check does not.
//
// Borrowed from the blog engine, including the warning band: a hard limit with no approach
// lights turns into an obstacle at the worst possible moment, when the next person to add two
// lines to a 399-line file has their change blocked by a check about a file they were not
// thinking about. Splitting is cheap when you choose the moment and expensive when the build
// chooses it for you.
//
// The GENERATED sheets are exempt, and that is not a loophole: nobody reads them, nobody
// edits them, and splitting `quireink-base.css` would mean the extractor deciding where to
// cut a file it copies verbatim.
import { readdirSync, readFileSync, statSync } from 'node:fs'
import { join } from 'node:path'

const MAX = 400
const WARN_AT = Math.floor(MAX * 0.95)
const ROOTS = ['quire-ink', 'tools', 'dev']
const EXT = /\.(ts|php|py|sh)$/

const walk = (dir: string): string[] =>
  readdirSync(dir).flatMap((name) => {
    const p = join(dir, name).replaceAll('\\', '/')
    return statSync(p).isDirectory() ? walk(p) : [p]
  })

// `assets/js` is Quire Ink's own bundled output, copied in. It is minified and it is not ours.
const exempt = (p: string) => p.includes('/assets/js/') || p.includes('/seed/json/')

const files = ROOTS.flatMap(walk).filter((p) => EXT.test(p) && !exempt(p))
const sized = files.map((p) => ({ p, n: readFileSync(p, 'utf8').split('\n').length }))

const over = sized.filter(({ n }) => n > MAX)
const near = sized.filter(({ n }) => n > WARN_AT && n <= MAX).sort((a, b) => b.n - a.n)

console.log(`  scanned ${files.length} file(s) (limit ${MAX} lines)`)
if (near.length > 0) {
  console.log(`  ${near.length} file(s) within ${MAX - WARN_AT} lines of the limit:`)
  for (const { p, n } of near) console.log(`  · ${p}: ${n}`)
}

if (over.length === 0) {
  console.log('✓ check:filesize: ok')
} else {
  console.log(`✗ check:filesize: ${over.length} violation(s)`)
  for (const { p, n } of over) console.log(`  · ${p}: ${n} lines`)
  process.exit(1)
}
