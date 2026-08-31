// Every class the templates print reaches a rule.
//
// This guard exists because of a defect that was invisible from every direction. The listing
// pages wore `list-head`, the pager `pagination t-small`, the empty state `t-small text-meta`.
// All three were spelled correctly, all three rendered, and none of them matched a rule: the
// sheet styles `.listing-head`, `.pager` and `.empty`, which are the blog engine's own names.
// The result was three blocks with no separation - a heading against its own list, a pager
// against the last excerpt - and nothing to see in a diff, because a class that matches
// nothing looks exactly like a class that matches something.
//
// The pager taught the second half of it: the theme asked for two classes and WordPress
// returned one. `the_posts_pagination()` runs the list through `sanitize_html_class()`, a
// function for a SINGLE class, which strips the space and hands back `paginationt-small`.
//
// A class listed in ALLOWED is a class with a reason. Anything else that no sheet styles is
// either a name the sheet has under a different spelling, or furniture nobody is drawing.
import { readFileSync, readdirSync, statSync } from 'node:fs'
import { join } from 'node:path'

const THEME = 'quire-ink'

/** Classes with no rule of ours, on purpose. */
const ALLOWED = new Map<string, string>([
  // WordPress or its block library owns the rule.
  ['wp-block-group', 'block library'],
  ['wp-block-columns', 'block library'],
  ['wp-block-column', 'block library'],
  ['wp-block-heading', 'block library'],
  ['wp-element-caption', 'block library'],
  ['has-alpha-channel-opacity', 'block library, on a separator'],
  ['size-large', 'core image size class'],
  // Aliased at render time by quireink_align_classes(), so the sheet never sees this name.
  ['is-style-frame', 'render_block aliases it to .img-frame'],
  ['alignwide', 'render_block aliases it to .img-wide'],
  // Printed for meaning, not for looks: they inherit from the block they sit in.
  ['byline', 'names the author inside an already-styled meta line'],
  ['info-terms', 'the blog engine emits this name too and styles neither'],
  ['required', 'the asterisk in a comment label; core themes colour it, this one does not'],
  ['page-links', 'wp_link_pages on a multi-page post; .pager would spread the numbers apart'],
  ['tagcloud', 'core widget markup, inside a rail block the theme does style'],
  ['tag-link-count', 'core widget markup'],
  ['widget', 'core widget wrapper, inside a rail block the theme does style'],
])

function walk(dir: string, ext: string, out: string[] = []): string[] {
  for (const name of readdirSync(dir)) {
    const path = join(dir, name)
    if (statSync(path).isDirectory()) walk(path, ext, out)
    else if (path.endsWith(ext)) out.push(path)
  }
  return out
}

// Anything with a rule, in any sheet the theme ships. generated-appearance.php is read as
// well: it PRINTS css, and a palette or a frame is styling like any other.
const sheets = [
  ...walk(join(THEME, 'assets/css'), '.css'),
  join(THEME, 'style.css'),
  join(THEME, 'rtl.css'),
  join(THEME, 'inc/generated-appearance.php'),
]
const styled = new Set<string>()
for (const file of sheets) {
  const css = readFileSync(file, 'utf8')
  for (const [, sel] of css.matchAll(/([^{}]+)\{/g)) {
    for (const [, cls] of sel!.matchAll(/\.([A-Za-z0-9_-]+)/g)) styled.add(cls!)
  }
}

// Every class a template writes into the markup as a literal.
const printed = new Map<string, Set<string>>()
for (const file of walk(THEME, '.php')) {
  // Comments first: a docblock explaining which class WordPress emits is prose, not markup,
  // and reading it as markup made this guard report a class that is only ever spoken about.
  const php = readFileSync(file, 'utf8')
    .replace(/\/\*[\s\S]*?\*\//g, ' ')
    .replace(/(^|\s)\/\/[^\n]*/g, ' ')
  for (const [, value] of php.matchAll(/class="([^"<>]*)"/g)) {
    for (const cls of value!.split(/\s+/)) {
      if (!/^[A-Za-z0-9_-]+$/.test(cls)) continue
      if (!printed.has(cls)) printed.set(cls, new Set())
      printed.get(cls)!.add(file.slice(THEME.length + 1))
    }
  }
}

const problems: string[] = []
for (const [cls, files] of [...printed].sort()) {
  if (styled.has(cls) || ALLOWED.has(cls)) continue
  problems.push(`.${cls} — printed by ${[...files].sort().join(', ')} — no rule in any sheet`)
}
// An allowance that has stopped being needed is a stale comment with a test around it.
const stale = [...ALLOWED.keys()].filter((c) => !printed.has(c))
for (const cls of stale) problems.push(`.${cls} is in ALLOWED but no template prints it any more`)

console.log(`  ${printed.size} class(es) printed by templates, ${styled.size} with a rule`)
if (problems.length === 0) {
  console.log(`✓ check:classes: ok (${ALLOWED.size} allowed by name, each with a reason)`)
} else {
  console.log(`✗ check:classes: ${problems.length} problem(s)`)
  for (const p of problems) console.log(`  · ${p}`)
  console.log('  the sheet usually has the block already, under the blog engine\'s own name')
  process.exit(1)
}
