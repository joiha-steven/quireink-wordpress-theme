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
| `quire-ink/assets/css/quireink-base.css` | `PUBLIC_CSS` — the hand-written public sheet, verbatim |
| `quire-ink/assets/css/quireink-tokens.css` | `pageStyles()`'s list, run on `DEFAULT_SETTINGS`, in the same order |
| `quire-ink/assets/fonts/*.woff2` | the self-hosted faces |
| `quire-ink/assets/js/{core,post}.js` | the reader bundles, as built |
| `quire-ink/theme.json` | the palette and type scale, in the block editor's dialect, pointing AT the variables rather than repeating their values |
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

## The sheets are minified, because that is what the blog serves

`PUBLIC_CSS` is the SOURCE of the blog engine's stylesheet. What a reader of that blog
downloads is `minifyCss(PUBLIC_CSS)` — `src/web/assets.ts` binds it to `PUBLIC_CSS_SERVED` and
does the same to the look sheets. Through 0.1.3 this extractor took the source and shipped it
unchanged, on the reading that "copied verbatim" meant the constant rather than the file the
blog puts on the wire.

Measured on 2026-09-21, the same sheet both ways:

| | Raw | Gzipped |
|---|---:|---:|
| What this theme shipped | 198,273 B | 59.8 KB |
| What demo.quireink.com serves | 68,942 B | 15.0 KB |

280 comment blocks, 65% of the file. They are the engine's notes to whoever next opens
`src/web/public.css.ts`, and a reader of a WordPress site was paying about 45 KB a visit to
download them. Nobody has ever read them there: [the file-size guard](../../tools/checks/file-size.ts)
exempts the generated sheets on exactly that argument.

The minifier is imported rather than written, so the bytes this ships are the bytes the blog
ships and `check:generated` compares them the same way. `rtl.ts` and `editor-css.ts` still read
the unminified strings — both walk rules and report a rule count as their evidence — and their
own output is minified on the way out.

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
