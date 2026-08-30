/**
 * Mirror a stylesheet, so the theme can ship an `rtl.css`.
 *
 * GENERATED, like everything else that comes out of the blog engine. A hand-written RTL sheet
 * would be 141 declarations retyped from a file this repository is not allowed to retype from,
 * and it would go stale the first time the engine moved a gutter.
 *
 * What comes out is a DIFF, not a copy: only the declarations that actually mirror, plus the
 * resets they need. WordPress appends `rtl.css` after every other sheet (`locale_stylesheet()`
 * on `wp_head`, after `wp_print_styles` at 8), so it overrides rather than replaces - and an
 * override that says `right:0` without also saying `left:auto` leaves a box pinned to both
 * sides. That reset is most of the care in here.
 *
 * Narrow on purpose. It knows the properties this sheet actually uses, and it WARNS rather
 * than guesses on anything else; `tools/extract.ts` prints the warnings and a person reads
 * them. A flipper that silently does its best is a flipper that silently gets it wrong.
 */

type Decl = { prop: string, value: string, important: string }
type Node =
  | { kind: 'rule', selector: string, decls: Decl[], seq: number }
  | { kind: 'at', prelude: string, children: Node[] }

/** left <-> right, on whole words only, so `border-top-left-radius` mirrors and `url()` cannot. */
const swap = (s: string): string =>
  s.replace(/\b(left|right)\b/g, (m) => (m === 'left' ? 'right' : 'left'))

// A physical property, and what to put back in its place once its value has moved across.
// Without this the LTR rule's own declaration is still in force underneath.
const RESET: Array<[RegExp, string]> = [
  [/^(left|right)$/, 'auto'],
  [/^(margin|padding|scroll-margin|scroll-padding)-(left|right)$/, '0'],
  [/^border-(left|right)$/, '0'],
  [/^border-(left|right)-width$/, '0'],
  [/^border-(left|right)-style$/, 'none'],
  [/^border-(left|right)-color$/, 'currentColor'],
  [/^border-(top|bottom)-(left|right)-radius$/, '0'],
]

// Properties whose VALUE names a side.
const VALUE_SIDED = new Set(['float', 'clear', 'text-align', 'background-position', 'object-position'])

// Four-part shorthands: top right bottom left, so second and fourth trade places.
const FOUR_PART = new Set([
  'margin', 'padding', 'inset', 'border-width', 'border-color', 'border-style',
  'scroll-margin', 'scroll-padding',
])

/** Split a value on top-level whitespace, so `calc(1rem + 2px)` stays one part. */
function parts(value: string): string[] {
  const out: string[] = []
  let depth = 0
  let cur = ''
  for (const ch of value) {
    if (ch === '(') depth++
    else if (ch === ')') depth--
    if (depth === 0 && /\s/.test(ch)) {
      if (cur) { out.push(cur); cur = '' }
      continue
    }
    cur += ch
  }
  if (cur) out.push(cur)
  return out
}

/** Negate a length, keeping it readable rather than wrapping everything in calc(). */
function negate(v: string): string {
  if (/^-?0(\D|$)/.test(v)) return v
  if (v.startsWith('-')) return v.slice(1)
  if (/^[\d.]/.test(v)) return '-' + v
  return `calc(-1 * ${v})`
}

function flipTransform(value: string, warn: (m: string) => void): string | null {
  // scaleX and its friends do not mirror by sign - a bar that grows from the start edge
  // mirrors through transform-origin, which is handled where that is declared.
  if (!/translate/i.test(value)) return null
  return value.replace(/\b(translateX|translate3d|translate)\(([^)]*)\)/gi, (_m, fn: string, args: string) => {
    const a = args.split(',').map((x) => x.trim())
    if (a[0] === undefined || a[0] === '') { warn(`empty ${fn}() in transform`); return `${fn}(${args})` }
    a[0] = negate(a[0])
    return `${fn}(${a.join(', ')})`
  })
}

function flipOrigin(value: string, warn: (m: string) => void): string | null {
  const p = parts(value)
  const first = p[0]
  if (first === undefined) return null
  if (/\b(left|right)\b/.test(first)) { p[0] = swap(first); return p.join(' ') }
  if (/^0%?$/.test(first)) { p[0] = '100%'; return p.join(' ') }
  if (first === '100%') { p[0] = '0'; return p.join(' ') }
  if (/^(center|top|bottom)$/.test(first)) return null
  warn(`transform-origin: ${value} — first value is not a side, 0 or 100%; left as it is`)
  return null
}

/**
 * One declaration in, zero or more out. Empty means it does not mirror.
 *
 * `siblings` is every property the rule declares, so a property whose mirror is also declared
 * here does not get a reset it does not need - the mirror will write the value back.
 */
function flipDecl(d: Decl, siblings: Set<string>, warn: (m: string) => void): Decl[] {
  const { prop, value, important } = d

  if (prop === 'transform') {
    const flipped = flipTransform(value, warn)
    return flipped === null ? [] : [{ prop, value: flipped, important }]
  }
  if (prop === 'transform-origin') {
    const flipped = flipOrigin(value, warn)
    return flipped === null ? [] : [{ prop, value: flipped, important }]
  }
  if (VALUE_SIDED.has(prop)) {
    if (!/\b(left|right)\b/.test(value)) return []
    return [{ prop, value: swap(value), important }]
  }
  if (FOUR_PART.has(prop)) {
    const p = parts(value)
    if (p.length !== 4) return []
    return [{ prop, value: [p[0]!, p[3]!, p[2]!, p[1]!].join(' '), important }]
  }
  if (prop === 'border-radius') {
    if (value.includes('/')) { warn(`border-radius: ${value} — the slash form is not mirrored`); return [] }
    const p = parts(value)
    if (p.length !== 4) return []
    return [{ prop, value: [p[1]!, p[0]!, p[3]!, p[2]!].join(' '), important }]
  }

  const mirror = swap(prop)
  if (mirror === prop) {
    // Nothing physical in the name. Logical properties (`margin-inline-end`, `inset-inline`)
    // land here and are LEFT ALONE, which is right: they already follow the direction.
    if (/\b(left|right)\b/.test(value) && !prop.startsWith('--') && prop !== 'content'
      && !value.includes('url(')) {
      warn(`${prop}: ${value} — a side in the value of a property the flipper does not know`)
    }
    return []
  }

  const out: Decl[] = [{ prop: mirror, value, important }]
  if (!siblings.has(mirror)) {
    const reset = RESET.find(([re]) => re.test(prop))
    if (reset) out.push({ prop, value: reset[1], important })
    else warn(`${prop} mirrors to ${mirror} but has no reset — the original stays in force`)
  }
  return out
}

// ------------------------------------------------------------------ the parser

function parseDecls(body: string): Decl[] {
  const out: Decl[] = []
  let depth = 0
  let cur = ''
  const push = (chunk: string): void => {
    const at = chunk.indexOf(':')
    if (at < 0) return
    const prop = chunk.slice(0, at).trim()
    let value = chunk.slice(at + 1).trim()
    let important = ''
    const bang = value.match(/!\s*important\s*$/i)
    if (bang) { important = '!important'; value = value.slice(0, bang.index).trim() }
    if (prop) out.push({ prop, value, important })
  }
  for (const ch of body) {
    if (ch === '(') depth++
    else if (ch === ')') depth--
    if (ch === ';' && depth === 0) { push(cur); cur = ''; continue }
    cur += ch
  }
  push(cur)
  return out
}

const NESTS = /^@(media|supports|layer|container|scope)\b/

// Document order, shared across the whole sheet including inside media queries, so "which
// declaration is the last word" can be answered later.
let seq = 0

function parse(css: string): Node[] {
  const nodes: Node[] = []
  let i = 0
  while (i < css.length) {
    while (i < css.length && /\s/.test(css[i]!)) i++
    if (i >= css.length) break
    const start = i
    while (i < css.length && css[i] !== '{' && css[i] !== ';') i++
    if (i >= css.length) break
    const prelude = css.slice(start, i).trim()
    if (css[i] === ';') { i++; continue } // @import, @charset: no body
    i++
    const bodyStart = i
    let depth = 1
    while (i < css.length && depth > 0) {
      if (css[i] === '{') depth++
      else if (css[i] === '}') depth--
      i++
    }
    const body = css.slice(bodyStart, i - 1)
    if (prelude.startsWith('@')) {
      // @font-face, @keyframes and the rest hold declarations, not rules, and none of them
      // has a side in it. Only the ones that WRAP rules are walked into.
      if (NESTS.test(prelude)) nodes.push({ kind: 'at', prelude, children: parse(body) })
    } else {
      nodes.push({ kind: 'rule', selector: prelude, decls: parseDecls(body), seq: seq++ })
    }
  }
  return nodes
}

// ------------------------------------------------------------------ the sheet

/** selector|property -> the highest sequence number that wrote it. */
type Last = Map<string, number>

function note(m: Last, selector: string, prop: string, at: number): void {
  const key = `${selector}|${prop}`
  if ((m.get(key) ?? -1) < at) m.set(key, at)
}

function render(nodes: Node[], warn: (m: string) => void, wrote: Last, indent = ''): string {
  const out: string[] = []
  for (const node of nodes) {
    if (node.kind === 'at') {
      const inner = render(node.children, warn, wrote, indent + '  ')
      if (inner) out.push(`${indent}${node.prelude}{\n${inner}${indent}}\n`)
      continue
    }
    const siblings = new Set(node.decls.map((d) => d.prop))
    const each = node.decls.map((d) => flipDecl(d, siblings, warn))

    // Did anything actually move? A rule full of `margin:0 auto` and `translateY` mirrors onto
    // itself and has no business being here.
    const moved = node.decls.some((d, i) => {
      const f = each[i]!
      return f.length > 1 || (f.length === 1 && (f[0]!.prop !== d.prop || f[0]!.value !== d.value))
    })
    if (!moved) continue

    // A rule that moved is restated IN FULL - the mirrored declarations and the ones the
    // flipper had nothing to say about, together.
    //
    // Not tidiness. Every rule in this file lands after the whole cascade it corrects, so a
    // declaration left behind here is a declaration this file silently outranks. The desktop
    // rail says `transform:none` to undo the phone drawer's translateX; `none` does not
    // mirror, so it was dropped, and the mirror's own `translateX(100%)` became the last word.
    // The rail sat exactly one rail-width too far out, and every rule that positioned it
    // measured correct. Restating the whole rule is what makes that impossible rather than
    // fixed.
    const emitted = node.decls.flatMap((d, i) => (each[i]!.length > 0 ? each[i]! : [d]))
    for (const d of emitted) note(wrote, node.selector, d.prop, node.seq)
    const body = emitted.map((d) => `${d.prop}:${d.value}${d.important}`).join(';')
    out.push(`${indent}${node.selector}{${body}}\n`)
  }
  return out.join('')
}

/** The ORIGINAL's last word on every selector+property, and the media it was said inside. */
type Said = { at: number, selector: string, decl: Decl, ctx: string[] }

function declared(nodes: Node[], m: Map<string, Said>, ctx: string[] = []): void {
  for (const node of nodes) {
    if (node.kind === 'at') { declared(node.children, m, [...ctx, node.prelude]); continue }
    for (const d of node.decls) {
      const key = `${node.selector}|${d.prop}`
      if ((m.get(key)?.at ?? -1) < node.seq) m.set(key, { at: node.seq, selector: node.selector, decl: d, ctx })
    }
  }
}

/** Rules that put back what the mirror would otherwise have outranked, in their own media. */
function tail(items: Said[]): string {
  const byContext = new Map<string, Said[]>()
  for (const s of items) {
    const key = s.ctx.join('\u0000')
    const list = byContext.get(key)
    if (list) list.push(s)
    else byContext.set(key, [s])
  }
  let out = ''
  for (const [key, list] of byContext) {
    const ctx = key === '' ? [] : key.split('\u0000')
    const bySelector = new Map<string, Decl[]>()
    for (const s of list) {
      const d = bySelector.get(s.selector)
      if (d) d.push(s.decl)
      else bySelector.set(s.selector, [s.decl])
    }
    const indent = '  '.repeat(ctx.length)
    let body = ''
    for (const [selector, decls] of bySelector) {
      body += `${indent}${selector}{${decls.map((d) => `${d.prop}:${d.value}${d.important}`).join(';')}}\n`
    }
    for (let i = ctx.length - 1; i >= 0; i--) {
      body = `${'  '.repeat(i)}${ctx[i]}{\n${body}${'  '.repeat(i)}}\n`
    }
    out += body
  }
  return out
}

/** The mirrored diff of a sheet, and everything the flipper was unsure about. */
export function flipCss(css: string): { sheet: string, warnings: string[] } {
  const warnings: string[] = []
  const seen = new Set<string>()
  const warn = (m: string): void => { if (!seen.has(m)) { seen.add(m); warnings.push(m) } }

  seq = 0
  const tree = parse(css.replace(/\/\*[\s\S]*?\*\//g, ''))
  const wrote: Last = new Map()
  const sheet = render(tree, warn, wrote)

  // THE DIFF CAN GO STALE INSIDE ITSELF, and this is the whole reason the file ends the way
  // it does. Every rule here lands after the cascade it corrects, so any property this file
  // writes, it writes LAST. Where a selector sets a property twice - once to position and
  // once later to undo, or once plainly and once for a safe-area inset - restating only the
  // first of the two resurrects a value the sheet had already retired.
  //
  // It is not hypothetical. The rail's `transform:none` went this way and put the sidebar one
  // rail-width out; `.to-top`'s `bottom` would have thrown away the notch inset. So: find
  // every property this file writes whose LAST word in the original comes later than what was
  // written, and say it again at the end, inside the same media query it was said in.
  const original = new Map<string, Said>()
  declared(tree, original)
  const stale: Said[] = []
  for (const [key, at] of wrote) {
    const last = original.get(key)
    if (last && last.at > at) stale.push(last)
  }
  stale.sort((a, b) => a.at - b.at)

  const restored = stale.length === 0 ? '' :
    '\n/* Said again, because the mirror above would otherwise be the last word on them. */\n'
    + tail(stale)

  return { sheet: sheet + restored, warnings }
}
