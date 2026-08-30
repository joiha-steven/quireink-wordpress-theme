# 0002 — Classic PHP templates, not a block theme

**In force. Worth revisiting once the markup stops moving.**

## Decision

`header.php`, `single.php`, `index.php` and the rest, with `theme.json` alongside for the
editor's palette and type scale. Not `templates/*.html` block templates.

## Why

Quire Ink's stylesheet binds to exact element names — `div.wrap`, `.with-rail`,
`main#content`, `aside.post-info`, `nav.toc.rail`, `#post-body.prose` — and several of its
rules win on a specificity tie alone. A classic template produces that markup byte for byte. A
block template produces whatever the block parser produces, and getting `<div class="wrap">`
out of it means fighting the parser at every level of the tree.

The question this project had to answer first was whether the look survives at all. Exact
markup was the shortest road to an honest answer.

## Cost

The owner cannot rearrange the page visually, which is most of the reason people want a block
theme in 2026. `theme.json` still gives the editor the right palette, type scale and spacing,
so what an author types looks like what gets published.

Both kinds are accepted on WordPress.org, so this closes no doors.

## When to revisit

Once the markup has stopped changing. Converting a settled layout to block templates is a
mechanical job; converting one that is still moving means doing it twice.
