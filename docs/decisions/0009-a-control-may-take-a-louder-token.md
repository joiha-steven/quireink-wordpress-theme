# 0009: A control's edge may take a louder token that already exists

**In force.** 2026-09-16.

## The decision

`bridge.css` may set a control's `border-color` to a **different `--c-*` variable than the
base sheet chose**, when a measured accessibility floor requires it. It may not invent the
colour. Today that is one rule: everything a reader types into, or presses, borders on
`--c-meta` instead of `--c-rule`.

This is the only override of its kind, and it is expected to stay the only one.

## Why

`--c-rule` draws the hairline between two cards. It is decorative and it is quiet on purpose:
measured from `inc/generated-appearance.php`, **1.26 to 1.35:1** against its own paper, in all
six palettes and both schemes.

The blog engine also gives a text input that same hairline as its only boundary. WCAG 2.1
SC 1.4.11 asks for **3:1** on anything a reader needs in order to identify a control, so a
form field fails it by a wide margin, and that single criterion is what has kept
`accessibility-ready` off this theme since it was first submitted.

`--c-meta` is the next token up: the colour of dates, counts and small print, **5.05 to
5.34:1** against the same papers. It is the lightest variable that already exists and already
clears the floor, so bordering a control with it decides nothing about colour. It follows the
palette, it follows the reader's light or dark choice, and it needs no new value.

## Why not upstream, which is where `conventions/css.md` sends a value like this

Because Quire Ink is a different project. It is a released product with production instances,
and this repository reads it. A defect there is worth fixing on that product's evidence and
that product's timing, not because a theme wants a directory tag; this was tried the other way
round on 2026-09-16 and reverted the same day.

The convention still holds for everything it was written for: a value that does not exist as a
variable still belongs upstream, and this rule invents no value. What it does is choose
between two variables the engine already ships, which is a decision about which register a
control belongs in, and that is a decision a theme is allowed to make about its own surface.

## What it costs

The theme and the engine now disagree about one property of one kind of element. That is drift,
and drift is what `conventions/css.md` exists to prevent, so it is written down here and it is
guarded rather than remembered:

* `check:contrast` recomputes the ratio from what the theme actually ships and fails under 3:1.
* It also reads the sheets for controls that still border on `--c-rule`, because a re-extract
  that quietly reverted this would leave every ratio true and the tag still declared.

If the engine ever answers this on its own, this override is deleted and the ADR is superseded,
not kept for symmetry.

## What it does not do

It does not touch the hairline between cards, the table striping, the rule under the header,
or any other use of `--c-rule`. It does not change a focus ring: the base sheet already moves
focus to `--c-heading`, which is 16.9:1 and was never the problem.
