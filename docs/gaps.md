# What does not carry over

Measured, not guessed: every line here was found by putting the same article through both
and looking at the result. The comparison is `tools/shot.sh` against
`https://manhhung.me/dung-synology-de-host-mot-cai-blog-khong-phai-wordpress`.

## Content: the block editor cannot express three things

`dev/seed/fetch.py` reports these by name for every article it converts, rather than dropping
them quietly, because a converter that silently loses them would make the screenshots agree
by deleting the evidence.

| Quire Ink | What happens in WordPress |
|---|---|
| `<mark data-pen="N">`, `<u data-pen="N">` — the pen | Renders correctly (the theme ships the ink sheet), but there is no block for it. An author typing in Gutenberg cannot make one; only imported content has them. |
| `<pre class="shiki">` — highlighted code | Renders as a plain code panel. Quire Ink highlights server-side with Shiki and emits per-token spans; WordPress's code block is plain text, and the window frame keys off `pre.shiki`, so it does not draw. |
| Footnotes (`sup.fnref` + `.footnotes`) | The CSS is there; nothing produces the markup. |

A pen block and a highlighting step are both buildable. Neither is built.

## Chrome: two things are site configuration, not theme gaps

- **The logo.** manhhung.me has a handwritten wordmark; a fresh install shows the site name
  as text. Appearance → Customize → Site Identity.
- **The newsletter button** (`[@email]` in the header). Quire Ink's own newsletter posts to
  its API. The theme ships the button's markup and Quire Ink's `core.js` still wires the
  form, but the endpoint does not exist in WordPress, so the button is not printed. Wiring it
  to a WordPress mailing-list plugin is a decision, not an oversight.

## Language

The theme's strings are English and translation-ready (text domain `quireink`), but no
translation is shipped. On a Vietnamese blog the article furniture therefore reads
`2,738 words` / `14 min read` / `Tags` where Quire Ink reads `2.799 chữ` / `14 phút đọc` /
`Tag`. A `vi_VN` translation is about forty strings and is the next obvious piece of work.

## Numbers that are close but not equal

**Word count: 2,738 here against 2,799 on the live blog** for the same article, with the
reading time landing on 14 minutes either way. Both count the same way — strip markup, split
on whitespace — but they are not counting the same text: Quire Ink counts its Markdown
source, and the theme counts the rendered HTML after the block editor has been through it.
Link syntax, image alt text and the pen's own markup each shift the total slightly. The
reading estimate is what the number exists for and it agrees, so this is recorded rather than
chased.

## Speed

Not measured yet, and it will not match. Quire Ink serves ~114 KB for an article from a
single process with hashed immutable assets and half the sheet inlined. This theme sits on
WordPress: the base sheet is 135 KB uncompressed before the pen's 273 KB, and whatever
plugins the site has are on top. A fair measurement needs a clean install and is worth doing
before anyone claims a number.
