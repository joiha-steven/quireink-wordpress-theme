# Still to decide

## 1. The licence — and this one is a one-way door

`theme/style.css` currently declares **GPLv2-or-later**, because WordPress requires it: a
theme header without a GPL-compatible licence is not a WordPress theme, and the file could
not be written without saying something. **That declaration has not been agreed to.**

Quire Ink is [PolyForm Noncommercial](https://polyformproject.org/licenses/noncommercial/1.0.0)
with an additional permission. The copyright is one person's and `CONTRIBUTING.md` in that
repository already grants the right to relicense, so there is no permission to obtain — this
is a choice, not a blocker.

What the choice costs: **a version published under GPL cannot be withdrawn.** Quire Ink keeps
its own licence either way, but the CSS in this repository — which is the look, and the look
is the part that is hard to copy — becomes forkable and resellable by anyone, permanently,
from the moment it ships. There is no partial version of this.

Nothing has been distributed. While this repository is private the header is a line in a file
and nothing more. **Settle this before the repository goes public**, and if the answer is no,
the header comes out and the theme is a private artefact instead.

## 2. Classic templates, or a block theme

Built as a **classic theme with `theme.json`** — PHP templates, hand-written markup. That was
the right call for the first question, which was whether the look survives at all: Quire Ink's
stylesheet binds to exact element names (`div.wrap`, `.with-rail`, `nav.toc.rail`), and a
classic template can produce them byte for byte. A block theme cannot without fighting the
block parser.

The cost is that the owner cannot rearrange the page visually, which is most of the reason
people want a block theme now. Both are accepted on WordPress.org. Worth re-deciding once the
markup has stopped moving, not before.

## 3. Whether to submit to WordPress.org at all

Not started, and it should not be until 1 is settled. What it needs: `readme.txt`,
a screenshot, a pass through Theme Check, escaping audited against the review handbook, and
then a review queue measured in weeks. The queue is the real cost, not the code.
