// style.css and readme.txt say the same thing.
//
// WordPress.org reads BOTH files and does not reconcile them: `style.css` is what the
// directory files the theme under, `readme.txt` is what a reader sees on the page. They drift
// the moment one is edited and the other is not, and neither file looks wrong on its own.
//
// It was already drifting when this was written. `style.css` declared 17 tags and `readme.txt`
// 10, missing every tag added after it was first typed - `rtl-language-support`,
// `block-patterns`, `editor-style`, `two-columns`, `left-sidebar`, `sticky-post`,
// `theme-options`. The theme had grown four capabilities and told half the story about them.
//
// `Version` and `Stable tag` are the same number under two names, which is a submission
// requirement rather than a nicety: a mismatch is what makes the directory serve one version
// and describe another.
//
// THERE IS A THIRD COPY OF THAT NUMBER, and this guard could not see it for a whole release.
// `QUIREINK_VERSION` in functions.php is the `?ver=` on every stylesheet and script the theme
// enqueues, so a stale value hands a returning reader last release's cache of a sheet that has
// changed. It sat at 0.1.0 through the whole of 0.1.1: both files this guard reads agreed with
// each other, and both disagreed with what the browser was actually asked to fetch.
import { readFileSync } from 'node:fs'

const CSS = 'quire-ink/style.css'
const README = 'quire-ink/readme.txt'
const FUNCTIONS = 'quire-ink/functions.php'

const css = readFileSync(CSS, 'utf8')
const readme = readFileSync(README, 'utf8')
const functions = readFileSync(FUNCTIONS, 'utf8')

const field = (src: string, name: string): string | null => {
  const m = new RegExp(`^${name}:[ \\t]*(.+)$`, 'm').exec(src)
  return m ? m[1]!.trim() : null
}

// Same field name in both files, and the value has to match exactly.
const SHARED = ['Requires at least', 'Tested up to', 'Requires PHP', 'License', 'License URI']
// Same value, different name. WordPress asks for the version twice and rejects a mismatch.
const RENAMED: Array<[string, string]> = [['Version', 'Stable tag']]

const problems: string[] = []
let compared = 0

for (const name of SHARED) {
  const a = field(css, name)
  const b = field(readme, name)
  compared++
  if (a === null) problems.push(`${CSS} has no "${name}:"`)
  else if (b === null) problems.push(`${README} has no "${name}:"`)
  else if (a !== b) problems.push(`${name}: style.css says "${a}", readme.txt says "${b}"`)
}

for (const [inCss, inReadme] of RENAMED) {
  const a = field(css, inCss)
  const b = field(readme, inReadme)
  compared++
  if (a === null || b === null) problems.push(`missing ${inCss}/${inReadme}`)
  else if (a !== b) problems.push(`${inCss} is "${a}" but ${inReadme} is "${b}" — they are one number`)
}

// The third copy: the cache-busting version the sheets and scripts are served under.
const constVersion = /define\(\s*'QUIREINK_VERSION',\s*'([^']+)'\s*\)/.exec(functions)?.[1] ?? null
compared++
if (constVersion === null) {
  problems.push(`${FUNCTIONS} has no QUIREINK_VERSION`)
} else if (constVersion !== field(css, 'Version')) {
  problems.push(
    `QUIREINK_VERSION is "${constVersion}" but style.css says "${field(css, 'Version')}"`
    + ' — it is the ?ver= on every sheet and script',
  )
}

// Tags are a SET: order is presentation, membership is the claim.
const split = (v: string | null): string[] =>
  (v ?? '').split(',').map((t) => t.trim().toLowerCase()).filter(Boolean)
const cssTags = split(field(css, 'Tags'))
const readmeTags = split(field(readme, 'Tags'))
compared++
const onlyCss = cssTags.filter((t) => !readmeTags.includes(t))
const onlyReadme = readmeTags.filter((t) => !cssTags.includes(t))
if (onlyCss.length > 0) problems.push(`tags only in style.css: ${onlyCss.join(', ')}`)
if (onlyReadme.length > 0) problems.push(`tags only in readme.txt: ${onlyReadme.join(', ')}`)

// The name over the readme is the name the directory will show beside the one in style.css.
const themeName = field(css, 'Theme Name')
const readmeTitle = /^===\s*(.+?)\s*===/m.exec(readme)?.[1]
compared++
if (themeName !== readmeTitle) {
  problems.push(`Theme Name is "${themeName}" but readme.txt is headed "${readmeTitle}"`)
}

console.log(`  ${compared} field(s) across ${CSS}, ${README} and ${FUNCTIONS}, ${cssTags.length} tag(s)`)
if (problems.length === 0) {
  console.log('✓ check:headers: ok (all three agree)')
} else {
  console.log(`✗ check:headers: ${problems.length} disagreement(s)`)
  for (const p of problems) console.log(`  · ${p}`)
  console.log('  style.css is the one the directory files the theme under — fix readme.txt to match')
  process.exit(1)
}
