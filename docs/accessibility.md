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

**Form field borders were 1.26:1 against the page, and are 5.10:1 now.** WCAG 2.1 SC 1.4.11
asks for 3:1 on anything needed to identify a control, and a text input whose only boundary is
`--c-rule` did not come close: measured from `inc/generated-appearance.php`, **1.26 to 1.35:1**
across all six palettes and both schemes, tightest at `#dcdfe1` on `#f6f8f7`.

`--c-rule` is the hairline between two cards. It is decorative and quiet on purpose, and it was
doing a second job it is the wrong weight for.

**Answered in `bridge.css` on 2026-09-16** by moving a control's edge to `--c-meta`, the next
token up and the lightest one that already clears the floor: 5.05 to 5.34:1 on the same papers.
No colour is invented, it follows the palette and the reader's light or dark choice, and the
card hairline is untouched. Why it is answered here rather than upstream, where
[`conventions/css.md`](conventions/css.md) would normally send it, is
[ADR 0009](decisions/0009-a-control-may-take-a-louder-token.md): Quire Ink is a different
project with production instances, and a defect there is worth fixing on that product's
evidence and timing, not because a theme wants a tag.

Read off the rendered page in a real WordPress rather than off the source, with the stylesheets
cache-busted first: the name, email, website and comment fields and the submit button all come
back `#6d6c6c` on `#fcfcfc`, **5.10:1**.

## So the tag goes on

`style.css` and `readme.txt` declare **`accessibility-ready`** from 0.1.4, alongside
`rtl-language-support`. Every criterion on the checklist is met and every one of them measured.

⚠️ **It is a claim a reviewer checks with a colour picker in a minute**, which is why it waited
eight days, and why it is held by a guard rather than by anyone's memory. `check:contrast` asks
two things of every change, and both were watched failing before they were trusted:

1. **The ratio.** `--c-meta` is already in this file's table at a 4.5:1 floor, so a re-extract
   that walked it under would fail there first.
2. **That a control still asks for it.** A tidy-up that dropped the `bridge.css` block would
   leave every ratio still true and the tag still declared, with the boundary back at 1.26:1 on
   the screen. The block is read for by name, selector by selector.
