# 0001 — The look is generated, never hand-copied

**In force.**

## Decision

`tools/extract.ts` imports Quire Ink's own stylesheet emitters from a sibling checkout and
runs them. No colour, size, breakpoint or font stack is typed into this repository by hand.
`check:generated` re-runs the extractor and compares bytes.

## Why

The alternative — read the values off the blog, retype them here — produces a copy that is
correct on the day it is made and wrong on some later Tuesday, with nobody able to say which
of the two is right. Quire Ink is under active development: it moved three commits in the
hour this theme was first written, and `quireink-base.css` changed size twice while it did.

Generating also makes the seam VISIBLE. When the blog engine changes something, the check goes
red and the diff says what moved. A hand-copied theme just drifts.

## Cost

The theme cannot be built without a Quire Ink checkout beside it. `check:generated` skips with
a warning rather than failing when there is none, so somebody cloning this repository alone
still gets the other five checks.

## The one exception

`quireink_shape_css()` in `quire-ink/inc/customizer.php` holds three tables copied from
`content/settings-shape.ts`, because a Customizer control cannot read TypeScript. Three lines
each, marked in place.
