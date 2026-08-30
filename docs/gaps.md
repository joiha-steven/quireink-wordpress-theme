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

**Eight block patterns** live as files under `quire-ink/patterns/`, which WordPress registers
by reading their header comments - not a block-theme feature, and it works here. Every one of
them exists for the same reason the Callout did: the sheet draws something the block editor
has no other way to ask for. `.deck`, `.callout`, the gutter-nosing wide figure, the picture
frames, and the table styling that ships and that nobody would otherwise find.

Writing them found a bug that a year of reading the sheet would not have. A pull quote is a
`<figure>` wrapping a `<blockquote>`, and the engine draws a rule down the left of ANY
blockquote - so the quote came out with two rules, the accent one on the figure and the
engine's hairline 16px inside it, and centred, because that is core's default and this theme
ranges quotes left. Both fixed in `bridge.css`. Nobody had inserted a pull quote before.

The two picture patterns ship no picture, and an author has to choose one: core renders an
image block with no `src` as **nothing at all** on the front end, though the editor shows its
placeholder and asks. That is the trade for a theme that bundles no images.

**Right to left** is a generated mirror, `quire-ink/rtl.css`, which WordPress links by itself
for an RTL locale - `locale_stylesheet()` on `wp_head`, after the sheets, no PHP of ours. It is
a DIFF: 141 directional declarations out of the four sheets, restated with the resets they
need, 13 KB raw and 3 KB gzipped, paid only by readers who are in an RTL locale. It mirrors
`bridge.css` too, so a hand-written rule that forgets RTL is answered without anyone
remembering to answer it, and `check:generated` goes red if one is added and not re-mirrored.

Two things it taught, both of them about a sheet that OVERRIDES rather than replaces. It lands
after the whole cascade, so every property it writes, it writes last: restating only the
declarations that mirror resurrected the phone drawer's `translateX` over the desktop rule's
`transform:none`, and the rail sat exactly one rail-width out while every rule that positioned
it measured correct. Whole rules are restated now. And where a selector says a property twice -
`.to-top` and its safe-area inset - the flipper says the original's last word again at the end,
inside the same media query. Verified by installing Arabic on the local stack: both gutters
40px, no horizontal overflow.

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
| `quireink-base.css` · `quireink-ide.css` | 39.0 + 6.5 KB (the second only when the switch is on) |
| `quireink-tokens.css` · `bridge.css` · `style.css` | 3.2 + 3.7 + 1.0 KB |
| `post.js` · `core.js` · WordPress's `comment-reply.js` | 6.5 + 4.1 + 1.4 KB |
| Fonts | 68.2 KB - **4 faces of the 21 declared.** Literata and JetBrains Mono, latin and vietnamese; the browser fetches a face only if a `unicode-range` it needs is in it |
| **First visit** | **≈ 152 KB**, or 146 KB with the IDE chrome off |
| **Every visit after** | **≈ 18 KB** - everything else is cached |

Quire Ink itself serves about 114 KB for the same shape of article, so the theme costs about
a third more, and the difference is WordPress's markup rather than the sheet.

The single largest item is the base sheet, and the biggest thing that could come out of it is
the IDE chrome — a treatment that is a taste, and one an owner can switch off. It is now its
own sheet, enqueued only when the switch is on. Measured both ways on the local stack:

| | CSS over the wire |
|---|---|
| Switch on (the default) | 53.4 KB |
| Switch off | 46.9 KB |

So **5.6 KB for a reader whose site has decided against it**, and **839 B against everyone
else**, because the same bytes compress a little worse in two files. It is a small win bought
with a small loss, taken because the loss falls on a request that is already open and the win
falls on bytes that could never be used.

The larger win is quieter: the block editor loads the sheets too, and not one rule in the IDE
chrome touches `.prose`, a post title or a comment body. It was 6.5 KB the editor could never
match, on every page load of the editor, and it is gone.

*(An earlier note here said 31 KB and a quarter of the sheet. That was a bad regular
expression reading section banners, not the sheet: it is 17,705 B raw, 13%.)*
