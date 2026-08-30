# Accessibility: what was measured, and the one thing that fails

Measured on the local stack, on a rendered article, in August 2026. Numbers rather than
intentions — every line here came out of a script or the browser, and the two defects it found
were both invisible on screen.

The point of the audit was to decide whether the theme may declare `accessibility-ready` in
`style.css`. **It may not, yet.** One thing fails, it is named at the bottom, and it is not
this repository's to fix.

## Colour, all six palettes, both schemes

[`check:contrast`](../tools/checks/contrast.ts) reads the palettes out of
[`inc/generated-appearance.php`](../quire-ink/inc/generated-appearance.php) and computes WCAG
contrast against each palette's own background — 60 colours, twelve combinations, and it is
part of `check:all` rather than a thing somebody once ran. The palettes are generated, so a
re-extract can walk a ratio under the line with nothing on screen looking different:

| Role | Floor | Worst measured |
|---|---|---|
| Body text | 4.5:1 | **10.22:1** (sepia, light) |
| Headings | 4.5:1 | **13.83:1** (sepia, light) |
| Meta and captions | 4.5:1 | **5.01:1** (sepia, light) |
| Links | 4.5:1 | **5.01:1** (sepia, light) |
| Accent marks | 3:1 (non-text) | **5.02:1** (forest, light) |

`--c-meta` is the tight one at 5.01:1, and the blog engine's own sheet says why: it was set
against a measurement, not by eye, and an earlier version at `opacity:.6` measured 2.26:1 —
which only ever looked acceptable because the IDE chrome resets the opacity, so the site the
owner sees was never the one shipping the failure.

## What else was checked, and passed

| | |
|---|---|
| One `h1` per page, no heading level skipped | article page: `h1` → `h2` → `h3`, no jumps |
| Landmarks | `header.site`, `<main id="content">`, `<nav>`, `footer.site` |
| Skip link | `.skip-link` → `#content`; `.skip-link:focus` moves it to 8px/8px with a background and a border, and nothing later in the cascade overrides it |
| Focus ring | `:focus-visible{outline:2px solid var(--c-accent);outline-offset:2px}`, and the accent measures 5.02:1 or better in every palette |
| Outline removed anywhere? | Once, on `.book-stage` — a reading surface that holds a dialog's initial focus, not a control. Documented as such upstream |
| Links in running text | `.prose a{text-decoration:underline}`, so they are not distinguished by colour alone |
| Images | every `img` the theme renders carries an `alt` |
| Form labels | every field in the comment form has a `<label for>` |
| Autoplay | none |
| `aria-hidden` hiding focusable things | none |
| `lang` | on `<html>`, from `language_attributes()` |

## The two defects it found

Both were controls that looked completely normal.

**Two buttons had no accessible name at all.** `label()` in the reader bundles is
`document.body.dataset[name] ?? ''`, so a key the theme does not supply is not a missing
translation — it is an empty string. Where that string is the `aria-label` of a button whose
entire content is an SVG, the button announces as "button". Back-to-top and the book-mode
button both shipped that way.

Diffing every key the bundles ask for against what
[`inc/i18n-data.php`](../quire-ink/inc/i18n-data.php) supplied found **24 of 33 missing**. Most
belong to the comment island, which never mounts here, and to offline reading, which is not
ported — those stay unsupplied on purpose, and the file says so. Thirteen were real, and seven
of the thirteen are inside book mode, where nothing is visible until the overlay is open.

**Form field borders are 1.16:1 against the page.** `--c-rule` is `#ebebeb` on `#fcfcfc`. WCAG
2.1 SC 1.4.11 asks for 3:1 on anything needed to identify a control, and a text input whose
only boundary is that hairline does not meet it. It is 1.16–1.33:1 in every palette.

This is the one that blocks the tag, and it is **not fixable here**. `--c-rule` is the blog
engine's value, the engine's own comment form draws its fields the same way, and overriding it
in `bridge.css` would be this theme deciding a colour — which is the thing
[`conventions/css.md`](conventions/css.md) exists to prevent. It belongs upstream, alongside
the OFL file noted in [`release-checklist.md`](release-checklist.md).

## So the tag stays off

`style.css` declares `rtl-language-support` because that is now true and tested. It does not
declare `accessibility-ready`, because one measured criterion fails. Declaring it would be a
claim a reviewer can disprove in a minute with a colour picker.

Everything else on the checklist is met. When the hairline is answered upstream and re-extracted,
this is a one-word change.
