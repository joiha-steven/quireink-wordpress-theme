# What does not carry over

Measured, not guessed: every line here was found by putting the same article through both and
looking at the result. The comparison is `tools/shot.sh` against
`https://manhhung.me/dung-synology-de-host-mot-cai-blog-khong-phai-wordpress`.

## Decided, not missing

Three of the four things that used to be on this list are now decisions rather than gaps, and
they are written up where a decision belongs:

- The pen, Shiki highlighting and footnotes —
  [ADR 0003](decisions/0003-skip-what-gutenberg-cannot-express.md). A WordPress author has no
  way to write that markup, so the theme does not ship the sheets that style it and the seeder
  strips it out of imported content.
- Vietnamese and every other language —
  [ADR 0004](decisions/0004-english-only.md). Translation-ready, no translations.

## Present, and worth knowing where it came from

The **left rail** on a listing page is the blog engine's discovery furniture, block for block:
the menu, then Featured, Categories, Archive and Tags. Featured is WordPress's **sticky
posts** - the platform already has "this one stays at the top", it is set per post in the
editor, and a Customizer field listing three post ids would be the same idea with worse
ergonomics.

One block has no counterpart and is not rendered: **Loạt bài / series**. WordPress has no
series taxonomy, and inventing one in a theme would put content structure in the layer that
is supposed to be about looks.

The **gutter timeline** - the spine, the sticky year tag, the month markers - is `timelineCss`
from the blog engine, extracted like everything else. Its breakpoint is deliberately lower
than the sidebar's: a month label needs about 130px of gutter and a sidebar needs 250px, so
the timeline still shows on a laptop where the sidebar has folded away.

**Comments** are WordPress's thread and form wearing Quire Ink's class names. The base sheet
already carried 35 rules for them, written for Quire Ink's own comment island, and they apply
to any markup that uses the same names. Four things that markup told us and a screenshot
confirmed:

* The walker has to override `html5_comment()` rather than `comment()`.
* `.comment-fields` is a layout for THREE fields, not two.
* **A container may not be opened in one field and closed in another.** `comment_form()`
  prints every field except `comment` only for a logged-OUT reader, so half of such a
  container simply does not print. `.comment-actions` opened in `cookies` and closed in
  `submit_field`: signed in, the closing tag ran on its own and closed `#respond` instead,
  the button lost the row and sat on the textarea, and the id fields landed outside the form
  element in the parsed DOM. The row is built in `submit_field` now, which always prints.
* **The consent checkbox cannot be removed by omission.** Core puts `cookies` back into any
  field list a theme passes without it, deliberately, so it has to be unset in
  `comment_form_fields`. Leaving it out printed the consent twice.

Two additions with no counterpart in the blog engine. WordPress prints a heading in front of
the form, outside it, because it doubles as the "Reply to X" target and carries the cancel
link; `.comment-form`'s top margin therefore opened below the title instead of above it, and
`#respond` takes that spacing now. And a signed-in reader gets a "Logged in as" line, which is
passed through `logged_in_as` wearing Quire Ink's `.comment-identity`.

## Site configuration, not theme gaps

- **The logo.** manhhung.me has a handwritten wordmark; a fresh install shows the site name as
  text. Appearance → Customize → Site Identity.
- **The newsletter button** (`[@email]` in the header). Quire Ink's newsletter posts to its own
  API. Quire Ink's `core.js` still carries the form's behaviour, but the endpoint does not
  exist in WordPress, so the button is not printed. Wiring it to a mailing-list plugin is a
  decision nobody has made.

## Close but not equal

**Word count: 2,738 here against 2,799 on the live blog** for the same article, with the
reading time landing on 14 minutes either way. Both count the same way — strip markup, split on
whitespace — but they are not counting the same text: Quire Ink counts its Markdown source and
the theme counts the rendered HTML after the block editor has been through it. Link syntax and
image alt text each shift the total slightly.

The reading estimate is what the number exists for and it agrees, so this is recorded rather
than chased.

## Measured: what an article costs

One article on the local stack, gzip as served, theme assets only - the post's own pictures
are content and are not counted, and no plugin is installed.

| | Over the wire |
|---|---|
| HTML | 18.3 KB (63.9 KB before gzip) |
| `quireink-base.css` | 44.7 KB |
| `quireink-tokens.css` · `bridge.css` · `style.css` | 3.2 + 3.7 + 0.9 KB |
| `post.js` · `core.js` · WordPress's `comment-reply.js` | 6.5 + 4.1 + 1.4 KB |
| Fonts | 68.2 KB - **4 faces of the 21 declared.** Literata and JetBrains Mono, latin and vietnamese; the browser fetches a face only if a `unicode-range` it needs is in it |
| **First visit** | **≈ 151 KB** |
| **Every visit after** | **≈ 18 KB** - everything else is cached |

Quire Ink itself serves about 114 KB for the same shape of article, so the theme costs about
a third more, and the difference is WordPress's markup rather than the sheet.

The single largest item is the base sheet, and the largest thing inside it is the IDE chrome:
**31 KB of 135 KB raw, a quarter of the sheet, for a treatment that is a taste and is on by
default.** Splitting it into a second sheet loaded only when the switch is on is the one
obvious cut, and it has not been made.
