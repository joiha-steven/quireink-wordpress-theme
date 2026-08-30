# 0005 — GPLv2 or later, which is what WordPress requires

**DECIDED, 2026-08-31. In force.** The theme ships under **GNU GPL v2 or later**.

## The decision

Whatever WordPress requires of a free theme, and nothing invented on top of it. The
[review handbook](https://make.wordpress.org/themes/handbook/review/required/) asks that a
theme be compatible with the GNU General Public License; any GPL-compatible licence is
accepted and GPLv2-or-later — WordPress's own — is what it prefers. So that is the line.

`quire-ink/style.css` already declared it, because a theme header could not be written without
putting something there. That declaration is now agreed to rather than provisional.

## What was actually being weighed

Quire Ink is [PolyForm Noncommercial](https://polyformproject.org/licenses/noncommercial/1.0.0)
with an additional permission for unmodified releases, and the CSS in this repository is
generated from it. PolyForm Noncommercial is **not** GPL-compatible, so the two licences could
not both apply to the same bytes.

The copyright is one person's and that repository's `CONTRIBUTING.md` already grants the right
to relicense, so there was no permission to obtain from anyone — the owner is putting *this
copy* of that work under GPL. **The blog engine keeps its own licence, unchanged.**

**A version published under GPL cannot be withdrawn.** The look becomes forkable and
resellable by anyone, permanently, from the moment it ships. That was the cost, it was stated
before the decision, and the decision was made with it on the table. It is not re-opened here.

## What the requirement actually is, item by item

| The handbook asks | Where it is answered |
|---|---|
| A GPL-compatible licence | GPLv2-or-later |
| `License:` and `License URI:` in `style.css` | both present, pointing at gnu.org |
| Licence, copyright and **source** for every bundled resource | `readme.txt` → Copyright: six typefaces, the two script bundles, the screenshot |
| That list in **one** file | `readme.txt`, and only there |

A separate `LICENSE` file is **not** required by the handbook. One is shipped anyway, because
it is the convention everywhere else and it costs 18 KB of text nobody has to read.

`quire-ink/assets/fonts/OFL.txt` is a different matter: that one is required by the **font
licence**, not by WordPress. The OFL asks that its text travel with the font software, so
shipping six OFL faces without it would be a licence violation whatever WordPress checked. Its
copyright block names all six holders; everything from the licence header down is SIL's text,
untouched.

*(The Quire Ink blog engine ships the same six faces and carries neither file. Same obligation,
different repository — still worth raising there.)*

## What is left before it can go public

Three fields only the owner can fill, listed in
[`../release-checklist.md`](../release-checklist.md): a real WordPress.org username for
`Contributors:`, a `Theme URI` distinct from the `Author URI`, and an honest `Tested up to:`.
None of them is a decision — they are facts nobody else knows.
