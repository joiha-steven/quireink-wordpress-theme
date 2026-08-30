# CSS

## Four sheets, one of them yours

| File | Written by | May be edited |
|---|---|---|
| `quireink-base.css` | `tools/extract.ts`, copied verbatim from the blog engine's `src/web/*.css.ts` | no |
| `quireink-tokens.css` | `tools/extract.ts`, by running the blog engine's own emitters on default settings | no |
| `bridge.css` | by hand | yes — and it is the only one |
| `style.css` | by hand | header only; it carries the theme's identity and no rules |

Editing a generated file is not wrong so much as pointless: the next `bun tools/extract.ts`
overwrites it, and `check:generated` goes red until it does.

## The order is load-bearing

`base` → `tokens` → `bridge`, wired by WordPress dependency rather than by call order. This is
[invariant 1](../invariants.md); the reasoning is there and it is worth reading before
touching `quireink_assets()`.

## What belongs in bridge.css

Translation, and nothing else. Gutenberg invents a class name or an extra element; the bridge
tells the existing sheet what it is. Two shapes cover almost everything:

**Alias the name in PHP, not the rule in CSS.** `alignwide` and `img-wide` are the same idea
with two names. The first version of the bridge copied the declarations across and had the
gutter measurement written down in two places within an hour; `check:bridge` caught it on the
4rem literal. `quireink_align_classes()` adds the Quire Ink class beside the WordPress one
instead, so there stays exactly one definition and it lives upstream.

**Restate nothing that the element selectors already cover.** `.prose p`, `.prose h2`,
`.prose blockquote`, `.prose pre`, `.prose table` are all styled by the base sheet, and
Gutenberg emits ordinary elements. Most blocks need no rule at all.

## No literals

No hex, no `rgb()`, no named colour, no length except `0`, `1px`, `2px`, `100%`. Colours come
from `--c-*`, sizes from `--fs-*`, spacing from `--sp`, corners from `--radius`. `check:bridge`
enforces it.

If a rule genuinely needs a value that does not exist as a variable, that is a signal the rule
belongs upstream in the blog engine, where it becomes a variable and the extractor brings it
across. It is not a signal to write the number here.

## The pen is gone, deliberately

Quire Ink's ink sheets are 273 KB of generated strokes keyed on `data-pen="0..N"` — attributes
only its own editor produces. They are not extracted
([ADR 0003](../decisions/0003-skip-what-gutenberg-cannot-express.md)).
