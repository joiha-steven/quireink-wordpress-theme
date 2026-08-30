# 0003 — What the block editor cannot author is not shipped

**In force.**

## Decision

Quire Ink markup that a WordPress author has no way to produce is not supported: the sheets
that style it are not extracted, and `dev/seed/fetch.py` strips it out of imported content.

Three things, at the time of writing:

| Gesture | What it needs | What happens now |
|---|---|---|
| The pen — `<mark data-pen="N">`, `<u data-pen="N">`, `<mark data-form=o>` | 273 KB of generated strokes keyed on attributes only Quire Ink's editor writes | sheet not extracted; attributes stripped on import |
| Shiki highlighting — `<pre class="shiki">` with per-token spans | a server-side highlighting pass | the token spans are stripped; a plain code panel remains, which is what WordPress's code block is |
| Footnotes — `sup.fnref` + `.footnotes` | a Markdown pipeline that collects them | nothing produces the markup; the CSS that would style it is inert |

## Why

The pen sheet shipped for one afternoon, conditionally linked, before anyone asked who would
author the markup it styles. The answer is nobody: there is no block, no inline format, and no
way to type one. It was 273 KB that could never match an element on a WordPress site.

Half-supporting it is worse than not supporting it. `<mark>` with the pen sheet REMOVED is a
plain element the base sheet leaves alone; `<mark>` with the sheet removed and the attributes
left in place would be the browser's yellow rectangle, which is the exact thing the pen exists
to avoid.

## What stripping means

`fetch.py` removes the gesture and keeps the words, and it NAMES every strip in its output:

```
stripped mark[data-pen]x24, u[data-pen]x2, pre.shikix60
```

A converter that quietly deleted them would make the side-by-side screenshots agree by
deleting the evidence.

## Reopening this

Each of the three is buildable — a pen block with an inline format, a highlighting step at
save time, a footnote block. Build the authoring side first. A stylesheet for markup nobody
can write is not support.
