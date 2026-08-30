// The generated sheets match what the blog engine produces TODAY.
//
// This is the guard the whole arrangement rests on. `quire-ink/assets/css/quireink-*.css` is a
// copy, and a copy nobody re-derives is a copy that silently stops being true: the blog
// engine moved three times in the hour this theme was first written, and the base sheet
// changed size twice while it did.
//
// It re-runs the extractor into `.tmp/` and compares bytes, rather than trusting the
// manifest's recorded commit - a manifest is a claim about a copy, and checking a copy
// against a claim written by the same run that made it proves nothing.
//
// SKIPS, loudly, when the sibling checkout is absent. Somebody cloning this repository alone
// should still get the other five checks rather than one red wall.
import { existsSync, readFileSync, readdirSync } from 'node:fs'
import { rm } from 'node:fs/promises'
import { join } from 'node:path'
import { $ } from 'bun'

const QUIRE = '../quireink'
const OUT = '.tmp/extract-check'
const CSS = 'quire-ink/assets/css'
const GENERATED = ['quireink-base.css', 'quireink-ide.css', 'quireink-tokens.css', 'editor.css']

if (!existsSync(QUIRE)) {
  console.log(`  ${QUIRE} not present`)
  console.log('⚠ check:generated: skipped — clone Quire Ink beside this repo to run it')
  process.exit(0)
}

await rm(OUT, { recursive: true, force: true })
const run = await $`EXTRACT_OUT=${OUT} bun tools/extract.ts`.quiet().nothrow()
if (run.exitCode !== 0) {
  console.log('✗ check:generated: the extractor failed')
  console.log(run.stderr.toString().trimEnd())
  process.exit(1)
}

const drift: string[] = []
for (const name of GENERATED) {
  const committed = readFileSync(join(CSS, name))
  const fresh = readFileSync(join(OUT, 'assets', 'css', name))
  if (!committed.equals(fresh)) {
    drift.push(`${name}: committed ${committed.length} B, fresh ${fresh.length} B`)
  }
}

// rtl.css lives in the theme ROOT, not under assets/css, because that is where WordPress
// looks for it. It is also the one generated file derived from a HAND-written source as well
// as from the blog engine: an edit to bridge.css that forgets to mirror shows up here.
{
  const committed = readFileSync('quire-ink/rtl.css')
  const fresh = readFileSync(join(OUT, 'rtl.css'))
  if (!committed.equals(fresh)) {
    drift.push(`rtl.css: committed ${committed.length} B, fresh ${fresh.length} B`)
  }
}

const fonts = readdirSync(join(CSS, '..', 'fonts')).length
const freshFonts = readdirSync(join(OUT, 'assets', 'fonts')).length
if (fonts !== freshFonts) drift.push(`fonts: ${fonts} committed, ${freshFonts} upstream`)

console.log(`  compared ${GENERATED.length + 1} sheet(s) and ${fonts} face(s) against ${QUIRE}`)

if (drift.length === 0) {
  console.log('✓ check:generated: ok')
} else {
  console.log(`✗ check:generated: ${drift.length} file(s) have drifted from the blog engine`)
  for (const d of drift) console.log(`  · ${d}`)
  console.log('  fix: bun tools/extract.ts, then look at the diff before committing it')
  process.exit(1)
}
