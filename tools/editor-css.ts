/**
 * The article's own typography, again, for the block editor.
 *
 * WHY THIS EXISTS. On the page, everything a reader sees inside a post is scoped to `.prose`:
 * the reading face, the measure, the heading scale, the list indents, the blockquote rule, the
 * hand-drawn link underline. In the editor there is no `.prose` — the canvas is a bare
 * `.editor-styles-wrapper` — so none of it applied, and an author wrote in JetBrains Mono at
 * 608px and published in Literata at 672px. Every static check passed the whole time. It is
 * the difference between a theme that works and a theme that feels finished, and it was
 * invisible from the front end.
 *
 * HOW. Every rule whose selector STARTS with `.prose` is re-emitted with that first component
 * rewritten to `.editor-styles-wrapper`, which is the canvas body.
 *
 * Not `body`, and that part is measured: the editor writes its own `<style>` blocks after every
 * enqueued link, including the base sheet's `body{font-family:var(--font-sans)}`. A `body` rule
 * ties with that and loses on order, which is how the canvas kept rendering in the mono chrome
 * face while everything else in this file was already working. No dependency fixes it — a
 * `<link>` cannot be printed after a `<style>` the editor injects itself.
 *
 * The class is written TWICE as insurance rather than as a fix for any one tie: this sheet
 * always arrives before the editor's own, so anything it means to win it has to win on weight.
 * One class was enough for every rule that was checked; two costs 2 KB and stops the question
 * being asked again the next time WordPress moves a default.
 *
 * It is safe to out-specify WordPress here and NOT the author. Everything chosen in the sidebar
 * arrives as an inline style or as a preset class carrying `!important` — checked, not assumed:
 * a paragraph set to Large still measures 36px against this sheet's 18px.
 *
 * Generated, for the reason everything here is generated: these are the blog engine's rules,
 * and a hand-written editor sheet is a second copy of the article's typography that drifts the
 * first time either moves.
 */
import { parse, type Node } from './rtl'

const CANVAS = '.editor-styles-wrapper.editor-styles-wrapper'

/**
 * Split a selector list on its own commas, not on the ones inside `:is()`.
 *
 * Splitting naively turned `.prose > :is(h1,h2,h3):first-child` into three fragments, two of
 * them with an unclosed paren, and a browser drops a rule whose selector will not parse. Sixty
 * of sixty-nine rules were being thrown away on the floor and the nine that survived were
 * enough to make the canvas look almost right, which is why it took a rule count to notice.
 */
function splitSelectors(list: string): string[] {
  const out: string[] = []
  let depth = 0
  let cur = ''
  for (const ch of list) {
    if (ch === '(' || ch === '[') depth++
    else if (ch === ')' || ch === ']') depth--
    if (ch === ',' && depth === 0) { out.push(cur); cur = ''; continue }
    cur += ch
  }
  out.push(cur)
  return out
}

/** `.prose` only counts when it is the first thing in the selector. */
function rewrite(selector: string): string | null {
  const head = selector.trimStart()
  if (head.startsWith('.prose')) {
    const rest = head.slice('.prose'.length)
    // `.prose.something` is a compound, not a descendant: rewriting the class off the front
    // would leave the canvas wearing a class it does not have.
    if (rest !== '' && !/^[\s>+~]/.test(rest)) return null
    return (CANVAS + rest).trim()
  }
  // `.book-text .prose p`, `html[data-ide-chrome=on] .prose ...`: the ancestor is a state the
  // editor canvas never has, so the rule cannot fire there and has no business being copied.
  return null
}

function render(nodes: Node[], indent = ''): string {
  let out = ''
  for (const node of nodes) {
    if (node.kind === 'at') {
      const inner = render(node.children, indent + '  ')
      if (inner) out += `${indent}${node.prelude}{\n${inner}${indent}}\n`
      continue
    }
    const kept = splitSelectors(node.selector).map(rewrite).filter((s): s is string => s !== null)
    if (kept.length === 0 || node.decls.length === 0) continue
    const body = node.decls.map((d) => `${d.prop}:${d.value}${d.important}`).join(';')
    out += `${indent}${kept.join(',')}{${body}}\n`
  }
  return out
}

/** The article's rules, addressed at the editor canvas instead of at `.prose`. */
export function editorCss(css: string): { sheet: string, rules: number } {
  const sheet = render(parse(css.replace(/\/\*[\s\S]*?\*\//g, '')))
  return { sheet, rules: (sheet.match(/\{/g) ?? []).length }
}
