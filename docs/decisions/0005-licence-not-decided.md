# 0005 — The licence is not decided

**OPEN. Settle before this repository goes public.**

## The state of things

`quire-ink/style.css` declares **GPLv2-or-later**, because a WordPress theme header cannot say
anything else — a theme without a GPL-compatible licence is not a WordPress theme, and the
file could not be written without putting something on that line.

**That declaration has not been agreed to.** Nothing has been distributed; while the
repository is private it is a line in a file and nothing more.

## What there is to decide

Quire Ink is [PolyForm Noncommercial](https://polyformproject.org/licenses/noncommercial/1.0.0)
with an additional permission for unmodified releases. The copyright is one person's, and that
repository's `CONTRIBUTING.md` already grants the right to relicense, so there is no
permission to obtain from anyone. This is a choice.

## What the choice costs

**A version published under GPL cannot be withdrawn.** Quire Ink keeps its own licence either
way. But the CSS in this repository is the look, the look is the part that is hard to copy,
and the licence is currently the only thing protecting it. Under GPL it becomes forkable and
resellable by anyone, permanently, from the moment it ships. There is no partial version of
this and no taking it back.

## The two answers

**Yes.** The theme goes to WordPress.org, which is one of the few genuinely large free
distribution channels that exists, and the name travels with it. Then: `readme.txt`, a
screenshot, Theme Check, an escaping audit against the review handbook, and a review queue
measured in weeks. The queue is the real cost, not the code.

**No.** The GPL line comes out of `style.css`, the theme stays private, and it is an artefact
for one person's own sites. Everything else in this repository works exactly the same.

## What "yes" costs after the decision

Not much, and it is listed in [`../release-checklist.md`](../release-checklist.md). Theme
Check is already clean of REQUIRED and WARNING findings; what remains is two verbatim legal
texts to fetch (the GPL and the OFL), three fields only the owner can fill, and a review queue
measured in weeks.

## Until then

The repository stays private, and this file is linked from the README so nobody can flip it
public without reading it.
