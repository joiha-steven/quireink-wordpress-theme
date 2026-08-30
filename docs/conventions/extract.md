# The extractor

`bun tools/extract.ts`

## What it does

Imports Quire Ink's own stylesheet emitters from `../quireink` and runs them. Every colour,
size, breakpoint and font stack in this theme is therefore the value the live blog renders
with, not a value somebody read off a screen and retyped.

`tools/tsconfig.json` maps `@/*` at the sibling checkout, which is the entire interface
between the two repositories. It is read-only: nothing here writes to Quire Ink, ever
([invariant 5](../invariants.md)).

## What it emits

| Output | From |
|---|---|
| `theme/assets/css/quireink-base.css` | `PUBLIC_CSS` — the hand-written public sheet, verbatim |
| `theme/assets/css/quireink-tokens.css` | `pageStyles()`'s list, run on `DEFAULT_SETTINGS`, in the same order |
| `theme/assets/fonts/*.woff2` | the self-hosted faces |
| `theme/assets/js/{core,post}.js` | the reader bundles, as built |
| `theme/theme.json` | the palette and type scale, in the block editor's dialect, pointing AT the variables rather than repeating their values |
| `tools/extract-manifest.json` | what came from where, and at which commit |

## The order in quireink-tokens.css is `pageStyles()`'s order

Read the two side by side when either moves. `--density` must be declared before the block
that multiplies it into `--sp`; the palette before the type scale. Four entries of that list
are left out and all four for the same reason: on default settings they emit the empty string.
`selectionCss` and `penGesturesCss` only speak when an owner has changed an ink or switched a
gesture off, and `figureCss`/`galleryCss` say nothing when the site-wide frame is `none`. Each
becomes a Customizer control here on the day it does not.

Three things that were missing from the first version, each invisible in a diff and obvious on
a screen:

- **the font handles** (`--font-sans`, `--font-reading`, `--font-mono`) — without them `.prose`
  fell back to the browser's serif while the rest of the page looked right;
- **`--shell-w`** — the column silently ignored the reading-width setting;
- **`singleRailCss()`** — the generated desktop rail geometry, without which the rail is a
  slide-out drawer at every width.

## Fonts are re-based, once

Quire Ink serves faces from the site root; a theme is a folder under `wp-content`. The
extractor rewrites `url('/fonts/` to `url('../fonts/`, which needs no PHP: a `url()` in a
stylesheet resolves against that stylesheet, and the two directories are siblings.

## Keeping it honest

`check:generated` re-runs the extractor into `.tmp/` and compares bytes. It does not trust the
manifest — a manifest is a claim about a copy, written by the same run that made the copy.

The blog engine moves. It moved three times in the hour this theme was first written, and the
base sheet changed size twice while it did. A red `check:generated` is not a failure, it is
the seam reporting: re-run the extractor and READ the diff before committing it.
