# Before this goes public

Measured, not remembered. `dev/check-theme.sh` runs the gate WordPress.org actually uses; as
of the last run it reports **0 REQUIRED, 0 WARNING, 3 RECOMMENDED**, and the three are answered
in [`decisions/0007-four-recommendations-declined.md`](decisions/0007-four-recommendations-declined.md).

## Blocking

**The licence.** [ADR 0005](decisions/0005-licence-not-decided.md) is open and it cannot be
undone once a version ships. Nothing below matters until it is settled.

## Two files that have to be fetched, not written

Both are verbatim legal texts. Reproducing either from memory would be worse than not shipping
it, so they are listed here rather than approximated.

* **`LICENSE`** — the full GNU GPL v2 text, from `https://www.gnu.org/licenses/gpl-2.0.txt`.
  `style.css` and `readme.txt` both declare the licence and link the canonical URL, which is
  what Theme Check checks; a repository carrying the text as well is the convention.
* **`quire-ink/assets/fonts/OFL.txt`** — the SIL Open Font License 1.1, from
  `https://openfontlicense.org/`. All six bundled families are OFL 1.1 and every one of them
  is credited by name, copyright holder and source in `readme.txt`. The OFL asks that the
  licence travel with the fonts, so the file belongs beside them.
  *(The Quire Ink blog engine ships the same six faces and carries neither file. Same
  obligation, different repository — worth raising there.)*

## Three fields only the owner can fill

* **`Contributors:` in readme.txt** is currently `joihasteven` and must be a real
  WordPress.org username, or the submission is rejected.
* **`Theme URI` and `Author URI`** in `style.css` both point at `quireink.com`. Reviewers
  expect the first to be the theme's own page and the second the author's; one page cannot
  honestly be both.
* **`Tested up to:`** has to name a WordPress version the theme was actually opened in. It
  says 6.8 and the local stack runs 6.8, which is true today and stops being true on its own.

## Worth doing, not required

* **The screenshot** is a render of a real blog, with its posts and its site name. It is
  honest and it contains no third-party images, so it passes — but a theme browsed by
  strangers reads better with demo content that is not one person's diary.
* **`rtl.css`.** The theme has none. Nothing in it is hostile to RTL, and nothing in it has
  been tried in RTL either, so this is untested rather than unsupported.
* **A `.zip` for submission** must be named `quire-ink.zip` and contain a single `quire-ink/`
  directory. The repository is already laid out that way, so this is `git archive` and nothing
  more — no build step, no compilation, no bundler.

## Already done

| | |
|---|---|
| Slug and text domain | `quire-ink`, matching the theme name, in the directory and every string |
| `readme.txt` | description, FAQ, copyright, per-font credits, changelog |
| `screenshot.png` | exactly 1200x900 |
| Copyright notice | in `style.css` and `readme.txt` |
| Escaping | every printed value; `check:escape` pins it |
| Prefixes | `quireink_` on every global |
| `post_class()` | article, page and every listing row |
| Theme supports | title-tag, post-thumbnails, custom-logo, align-wide, html5, editor styles, responsive embeds, feed links, menus |
| Featured images | two shapes, both off by default |
| Core CSS classes | `align*`, `wp-caption*`, `gallery-caption`, `bypostauthor`, `sticky`, `screen-reader-text` |
| Block patterns and styles | eight patterns under `quire-ink/patterns/`, in their own inserter category and a core one each, plus four image frames as block styles - all of them reaching rules the blog engine already carries |
| Translation | `languages/quire-ink.pot`, 160 strings, no translations shipped ([ADR 0004](decisions/0004-english-only.md)). Rebuild it with `bun run pot` after touching any string |
| Templates | index, single, page, archive, search, 404, comments, searchform |
| Accessibility | skip link, focusable off-screen text, no avatars, no third-party requests |
