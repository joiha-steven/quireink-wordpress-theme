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
| Skip link | `.skip-link` → `#content`; `.skip-link:focus` moves it to 8px/8px with a background and a border, and nothing later in the cascade overrides it. Clear of the admin bar — see below |
| Focus ring | `:focus-visible{outline:2px solid var(--c-accent);outline-offset:2px}`, and the accent measures 5.02:1 or better in every palette |
| Outline removed anywhere? | Once, on `.book-stage` — a reading surface that holds a dialog's initial focus, not a control. Documented as such upstream |
| Links in running text | `.prose a{text-decoration:underline}`, so they are not distinguished by colour alone |
| Images | every `img` the theme renders carries an `alt` |
| Form labels | every field in the comment form has a `<label for>` |
| Autoplay | none |
| `aria-hidden` hiding focusable things | none |
| `lang` | on `<html>`, from `language_attributes()` |

## Focus order, the admin bar, and a title nobody can break

Three more, all found by a WordPress.org reviewer on
[ticket #288845](https://themes.trac.wordpress.org/ticket/288845) and all measured here before
and after. The measurements are [`tools/kbd-probe.ts`](../tools/kbd-probe.ts): headless Chrome
driven over CDP, tabbing a real page and reporting what has focus, where it is, and whether it
is on screen. It is not part of `check:all` — it needs a running site and a browser — but it is
re-runnable, which is the difference between a measurement and a memory.

**The sidebar came last.** Above the rail breakpoint the rail sits in the LEFT gutter, level
with the first line — and it was printed from `footer.php`, so it came after `<main>` in the
document. On the listing page that is eight article links before the sidebar: `header → main
→ rail` where a reader sees `header → rail → main`, which is SC 2.4.3. It is printed from
`header.php` now, before `<main>`. Nothing moved on screen, because `.rail` is out of flow at
every width. Same move inside an article, where `quireink_toc()` now comes before the
right-hand meta column.

**And moving it exposed the drawer.** Below the breakpoint the same element is parked at
`translateX(-100%)`, off the left edge — still focusable, so putting it first would have meant
eight invisible stops before the article on every phone. The closed drawer is `inert` now,
from twelve lines inlined after `core.js`. Measured across eight templates at 1440, 700 and
390: **0 focus stops off screen**, and every rail link reachable the moment the drawer opens.

**The skip link was drawn behind the admin bar.** `.skip-link:focus` places itself 8px from
the top of the page; WordPress's bar is 32px tall, fixed, at z-index 99999. So on every page
of a logged-in site the first control a keyboard reached was under something else. Measured:
focused top 8px against a bar whose bottom is 32px. It now takes a `margin-top` of
`var(--wp-admin--admin-bar--height, 0px)` — WordPress's own declaration, 32px on a desktop
and 46px under 782px — and measures 40px and 54px, clear in both.

**A long title scrolled the page sideways.** One unbroken string in a post title is a single
word by every line-breaking rule there is: an 87-character title at 1440px gave the document
a scrollWidth of **1905px** against a 1440px viewport. `.wrap` carries `overflow-wrap:
break-word` now, inherited by every title, rail row, term and caption under it. Measured at
1440, 700 and 390 across eight templates: **0px of horizontal overflow** everywhere.

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

**Form field borders were 1.16:1 against the page, and are 3.55:1 now.** `--c-rule` is the
hairline between two cards, and a text input whose only boundary is that hairline did not meet
WCAG 2.1 SC 1.4.11, which asks for 3:1 on anything needed to identify a control. It measured
1.26 to 1.35:1 in every palette and both schemes.

**Answered upstream on 2026-09-16**, where it belonged: the blog engine's own comment form drew
its fields the same way, so this was never only a theme problem. `--c-field-edge` is a separate
promised variable there, mixed from `--c-text` and `--c-bg` at 58%, which is the first mix that
clears 3:1 on all twelve combinations while staying lighter than `--c-meta`. Worst case 3.55:1,
best 5.10. Extracted here like everything else, and read back off the rendered page in a
browser rather than off the source.

It was never fixable here. `--c-rule` is the blog engine's value and overriding it in
`bridge.css` would have been this theme deciding a colour, which is the thing
[`conventions/css.md`](conventions/css.md) exists to prevent. Waiting for upstream was the
right answer and upstream answered it.

## So the tag goes on

`style.css` and `readme.txt` declare **`accessibility-ready`** from 2026-09-16, alongside
`rtl-language-support`. Every criterion on the checklist is met and measured; the one that was
not is the section above, and it was answered in the blog engine and extracted rather than
patched here.

⚠️ **It is a claim a reviewer checks with a colour picker**, which is why it waited, and why it
is held by arithmetic rather than by anyone's memory. `check:contrast` asks two things of every
re-extract, and both were watched failing before they were trusted:

1. **The ratio.** It recomputes `--c-field-edge` from the two hexes the theme actually ships,
   the way a browser computes a `color-mix()`, and fails under 3:1. Tightest today is 3.55:1 on
   Mono light.
2. **That a control still asks for it.** A re-extract pointing a field's border back at
   `--c-rule` would leave every ratio above still true and the tag still declared, with the
   boundary back at 1.26:1 on the screen. Anything a reader types into is read for it by name.
