# Before this goes public

**0.1.4 is built and not submitted.** Measured on the local stack at WordPress 7.1.1:
`check:all` green on all ten guards, `dev/check-theme.sh` **0 REQUIRED, 0 WARNING,
3 RECOMMENDED**, every template answering (index, single, page, category, month, search, 404,
feed, a password-protected post, a paged post), `kbd-probe` at 1440 and 390 on the listing and
an article — 22 stops, none off screen, no horizontal overflow, the skip link clear of the
admin bar — and a clean browser tab making no request the reader did not ask for.

**It went public on 8 September 2026, at 0.1.3**, after two rounds of review on
[ticket #288845](https://themes.trac.wordpress.org/ticket/288845): five findings on 0.1.1 and
two more on 0.1.2. The listing is [wordpress.org/themes/quire-ink](https://wordpress.org/themes/quire-ink/).
What follows is the list that got it there, kept because the next release goes through the
same gate — and one line of it goes stale on its own, which is the point of saying so here.

Measured, not remembered. `dev/check-theme.sh` runs the gate WordPress.org actually uses; as
of the last run it reports **0 REQUIRED, 0 WARNING, 3 RECOMMENDED**, and the three are answered
in [`decisions/0007-four-recommendations-declined.md`](decisions/0007-four-recommendations-declined.md).

## Settled

**The licence: GPLv2 or later** ([ADR 0005](decisions/0005-gpl-v2-or-later.md)), which is what
the review handbook asks of a free theme. Both verbatim texts are fetched and in the tree:

* **`LICENSE`** — the GNU GPL v2, from `https://www.gnu.org/licenses/gpl-2.0.txt`. Not
  required by the handbook; shipped because it is the convention everywhere else.
* **`quire-ink/assets/fonts/OFL.txt`** — SIL Open Font License 1.1, from
  `https://openfontlicense.org/documents/OFL.txt`, its copyright block naming all six holders
  and the rest untouched. This one IS required — by the font licence rather than by
  WordPress, which is why it would still be needed if the theme never went to the directory.
  *(The Quire Ink blog engine ships the same six faces and carries neither file. Same
  obligation, different repository — worth raising there.)*

## The three fields only the owner could fill, and what each turned out to be

* ~~**`Contributors:`**~~ is `joihasteven`, and that was never a placeholder — it is the
  owner's account. Verified rather than assumed: `profiles.wordpress.org/joihasteven/`
  answers 200 and the page is titled for that user. A username in a file proves nothing about
  a username on a server.
* ~~**`Theme URI` and `Author URI`**~~ are two different pages now. `Theme URI` is the public
  repository, which is the theme's own page in the sense a reviewer means: the source, the
  history and these documents. `Author URI` stays `quireink.com`. The repository was private
  when this line was written, which is why it could not be the answer then — it was opened
  after reading 22 commits of history for keys, because publishing a repository publishes
  what it used to contain as well as what it contains.
* ~~**`Tested up to:`**~~ says **7.1**, which is what `wp core version` reports on the local
  stack and what every measurement in these documents was taken on. It had said 6.8, from the
  Docker image's tag rather than from the install — the tag is `wordpress:6.8-php8.3-apache`
  and the container updated itself past it. A version read off a filename is not a version the
  theme was opened in. It goes stale on its own, so re-read it before a release rather than
  trusting this line.

  **Re-reading it for 0.1.4 caught it lying.** The install reported 6.8.3, not 7.1: the 7.1
  that was measured for 0.1.3 was an in-place `wp core update`, `dev/down.sh` threw that volume
  away, and the next `up.sh` quietly built a 6.8 back from the image pin. The header was
  claiming a version no current checkout could reproduce. The install was updated to 7.1.1,
  every template and every measurement re-run on it, and **the compose pin moved to
  `wordpress:7.1-php8.3-apache`** — a header read off an install is only honest while the
  install is the one the pin builds.

Checking the last of them turned up a fourth thing nobody had listed. `style.css` declared
17 tags and `readme.txt` 10, missing every tag added after it was first typed —
`rtl-language-support`, `block-patterns`, `editor-style`, `two-columns`, `left-sidebar`,
`sticky-post`, `theme-options`. WordPress.org reads both files and reconciles neither: one is
what the directory files the theme under, the other is what a reader sees on the page, and the
theme had grown four capabilities while telling half the story about them.
[`check:headers`](../tools/checks/headers.ts) compares them now — the shared fields, the tags
as a set, `Version` against `Stable tag`, and the name over the readme against the theme name.

**The version number lives in THREE files, and the third one was wrong for a whole release.**
`QUIREINK_VERSION` in `functions.php` is the `?ver=` every sheet and script is served under, so
a stale value hands a returning reader last release's cache of a sheet that has changed. It sat
at `0.1.0` through the whole of 0.1.1 while `style.css` and `readme.txt` agreed with each other
and both disagreed with what the browser was asked to fetch. The guard reads all three now.

## Measured, and the one that said no has been answered

**Accessibility.** [`accessibility.md`](accessibility.md) has the numbers. Colour clears WCAG
AA in all six palettes and both schemes with room to spare, headings and landmarks and labels
and the skip link all pass, and the audit found two real defects on the way — two buttons with
no accessible name, and thirteen unsupplied strings behind them, now fixed.

`accessibility-ready` **is declared from 0.1.4**. Through 0.1.3 it was not, for one measured
reason: form field borders were 1.26:1 against the page where SC 1.4.11 asks 3:1. A control's
edge takes `--c-meta` now — the next token up, and the lightest that already clears the floor —
which measures 5.05 to 5.34:1 across all six palettes and both schemes, invents no colour and
leaves the card hairline alone ([ADR 0009](decisions/0009-a-control-may-take-a-louder-token.md)).

**Declaring it changes which queue the submission joins**: the tag puts a theme in front of the
accessibility reviewers rather than the general ones, and what they test is not what
`check:contrast` measures. [`accessibility.md`](accessibility.md) ends with the two things that
are disclosed rather than measured away, both of them about a page with scripting off, and both
are the answer if a reviewer raises them.

## Worth doing, not required

* ~~**The screenshot**~~ is built by [`dev/screenshot.sh`](../dev/screenshot.sh) from a
  WordPress seeded for the purpose: five posts whose words were written for the picture, two
  categories, five tags, a menu and one sticky post, so the rail has a Featured block and the
  headline has its bullet. Five and not three since 0.1.4 — at three the bottom third of the
  frame was the footer on white, which reads as an empty page at full size and as a blank card
  at the 387px the directory's own grid shows. It used to be a render of the owner's own blog — honest, and it
  passed, but a theme strangers browse should not open on one person's diary, and `readme.txt`
  now says the words in it were written for it, which is a sentence that has to stay true.

  It renders at 1440 CSS pixels and scales down to exactly 1200x900. Not at 1200: by that
  viewport the rail has already folded into the header's menu button, so a 1200-wide render
  shows the theme without the thing the theme is for. The script exports the database first
  and restores it from a trap, so an interrupted run does not cost a seed.
* ~~**`rtl.css`.**~~ Generated by [`tools/rtl.ts`](../tools/rtl.ts) and pinned by
  `check:generated`, which also catches a `bridge.css` edit that forgot to mirror. Checked by
  installing Arabic on the local stack and measuring both gutters: 40px each side, no
  horizontal overflow. `rtl-language-support` is declared.
* ~~**A `.zip` for submission**~~ is one command, because the repository is laid out the way
  the directory wants it — no build step, no compilation, no bundler:

  ```
  git archive --format=zip --prefix=quire-ink/ -o .tmp/dist/quire-ink.zip HEAD:quire-ink
  ```

  It builds from `HEAD`, not from the working tree, so an uncommitted edit cannot ship by
  accident. Measured on the last build: 732 KB, 64 files, exactly one top-level directory,
  and `diff -rq` against the working tree reports no difference — nothing gitignored is
  missing from it and nothing untracked is in it. Build it into `.tmp/`, which is gitignored:
  a release artifact committed to the repository is a second source of truth for the same
  bytes.

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
