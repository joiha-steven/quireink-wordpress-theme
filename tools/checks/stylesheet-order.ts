// Invariant 1: the sheets are enqueued base -> tokens -> bridge, and each depends on the one
// before it.
//
// THIS IS THE BUG THIS REPOSITORY EXISTS TO NOT REPEAT. The order is the blog engine's own:
// it links the static sheet and inlines the generated half LAST, because the generated half
// is generated precisely so that it can win. `.rail` is a slide-out drawer in the static
// sheet, and only the computed media query in the generated half promotes it into the
// desktop gutter.
//
// Enqueued the intuitive way round - variables first, since everything reads them - the
// drawer rule wins on source order and the table of contents silently never appears on any
// desktop screen. Nothing errors. Nothing logs. Three rounds of screenshots went past it.
//
// The dependency chain is what actually pins the order (WordPress emits by dependency, not by
// call order), so the chain is what is checked, not the sequence of the calls.
import { readFileSync } from 'node:fs'

const SRC = 'quire-ink/functions.php'
const ORDER = ['quireink-base', 'quireink-tokens', 'quireink-bridge', 'quireink-style']

const php = readFileSync(SRC, 'utf8')

// handle => the dependency array as written
const enqueued = new Map<string, string[]>()
const re = /wp_enqueue_style\(\s*'([^']+)'[^,]*,[^,]*,\s*array\(([^)]*)\)/g
for (const m of php.matchAll(re)) {
  const deps = [...m[2]!.matchAll(/'([^']+)'/g)].map((d) => d[1]!)
  enqueued.set(m[1]!, deps)
}

const problems: string[] = []

for (const handle of ORDER) {
  if (!enqueued.has(handle)) problems.push(`${handle} is not enqueued at all`)
}

for (let i = 1; i < ORDER.length; i++) {
  const handle = ORDER[i]!
  const previous = ORDER[i - 1]!
  const deps = enqueued.get(handle)
  if (deps === undefined) continue
  if (!deps.includes(previous)) {
    problems.push(
      `${handle} does not depend on ${previous} (declared: ${deps.join(', ') || 'none'})`
      + ` — WordPress may then emit it first, and the sheet that must win loses`,
    )
  }
}

console.log(`  ${enqueued.size} stylesheet(s) enqueued in ${SRC}`)
if (problems.length === 0) {
  console.log(`✓ check:order: ok (${ORDER.join(' -> ')})`)
} else {
  console.log(`✗ check:order: ${problems.length} problem(s)`)
  for (const p of problems) console.log(`  · ${p}`)
  process.exit(1)
}
