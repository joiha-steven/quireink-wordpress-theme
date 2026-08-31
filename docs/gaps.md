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

**The block editor shows the article, not a guess at it.** Everything a reader sees inside a
post is scoped to `.prose`; the editor canvas is a bare `.editor-styles-wrapper` and has no such
class, so for a long time none of it applied — an author wrote in the mono chrome face at 608px
and published in a book serif at 672px. Nothing about the PAGE was wrong, so nothing caught it.

`tools/editor-css.ts` re-addresses those same rules at the canvas and `quireink_editor_css()`
enqueues them into the iframe. Two things it taught:

* **`theme.json`'s widths were hand-typed and drifted.** `contentSize` was `38rem` against a
  reading column of 672px, and `wideSize` `52rem` against a wide figure of 962px, so the editor
  drew every block 64px narrower than the page would and "wide" meant two different things on
  the two sides of Publish. Both come from the engine's own numbers now.
* **A selector list is not a comma-separated string.** Splitting one naively shredded
  `.prose > :is(h1,h2,h3)` into fragments with unclosed parens, and a browser silently drops a
  rule whose selector will not parse: 60 of 69 rules were going on the floor, and the nine that
  survived left the canvas looking *almost* right. The rule count is what caught it, not the eye.

Measured after: Literata at 672px in both, headings matching, inline code in JetBrains Mono on
its tinted ground — and a paragraph set to "Large" in the sidebar still measures 36px, because
the author's own choices have to beat the theme and do.

**Every screenshot this repository had taken was rendered without the theme's JavaScript or
its fonts.** WordPress writes its asset URLs against `siteurl`, which on the local stack is
`http://localhost:8099`. Every document here, this one included, said to shoot
`http://127.0.0.1:8099`. Same server, different ORIGIN: each module script and each font is a
cross-origin fetch, CORS refuses all of them, and what renders is a page with no behaviour and
a metric fallback face.

It survived because the fallback is close and because the obvious check agrees with the wrong
answer. `getComputedStyle(p).fontFamily` returns `Literata` whether or not Literata arrived,
since a computed font-family is the request rather than the result. `document.fonts` is the
measurement, and it separated them at once: `Literata:loaded` twice on `localhost` against
`Literata:error` three times on `127.0.0.1`, with `Literata Fallback:loaded` in its place.

What it hid was worse than blurry type. **Book mode had never once been opened.** It is a
dialog built by `post.js` from a button click, `post.js` was among the scripts CORS was
refusing, and a click on the button did nothing, in every render and in the browser pane. It
works, and the picture in the README is the first time anyone has seen it: two columns, a drop
cap, a chrome bar with the type-size controls and a page count.

`tools/shot.sh` refuses the wrong origin now, naming the origins the page actually points at,
with `SHOT_ALLOW_CROSS_ORIGIN=1` for a site that means to serve assets from elsewhere. Every
picture in the repository was rebuilt.

**A phone was never actually photographed.** `tools/shot.sh` at 390px produced a page whose
body text ran off the right edge and whose header icons were sliced in half - a theme that
overflows, in a file exactly 390 pixels wide. It does not overflow. `headless=new` opens a
real window and the window has an OS minimum: Chrome laid the page out at 500 and returned
the left 390 pixels of it. Measured by rendering the same page at 600, 540, 500, 480, 460,
420 and 390: everything from 480 down reproduces the 500px render line for line, and only 500
and 600 wrap differently. The script refuses below 500 now, with the reason.

Measured properly, in device emulation at 375px, on every template: `scrollWidth` equals
`clientWidth` on all of them, so nothing scrolls sideways. The only elements outside the
viewport are outside it on the LEFT and meant to be - the skip link at -9999px and the closed
rail drawer at -300px. The reading column is 327px inside 375, the search form stacks to a
column, the comment fields collapse to one column and the comment actions wrap.

**Dark was checked by measurement rather than by eye**, because the numbers are the stronger
evidence: 21 elements across three templates, every one of them - text, ground and rule -
different under `html.dark` than without it, and none left behind. `check:contrast` already
holds the ratios for all six palettes in both schemes.

**Three blocks were wearing names no sheet has.** The listing pages said `list-head`, the
pager said `pagination t-small`, the empty state said `t-small text-meta` inside an
`article.reveal`. Every one of those spellings is correct, every one rendered, and not one of
them matched a rule: the sheet carries `.listing-head`, `.pager`, `.pager-count` and `.empty`,
which are the blog engine's own names for the same four blocks.

What that cost was separation, because separation is what those rules are mostly made of.
`.listing-head{margin:0 0 2rem}` never ran, so "Category: Selfhost" sat on the first post's
date and the search field sat on the first result, on three templates. `.pager` never ran, so
the pager arrived with no hairline over it and no top margin and read as one more line of the
last excerpt. Nothing was red. A class that matches nothing looks exactly like a class that
matches something.

The pager had a second cause underneath the first. The theme asked for two classes and
WordPress returned one: `the_posts_pagination()` puts its list through
`sanitize_html_class()`, which is a function for a SINGLE class and strips the space, so
`pagination t-small` reached the page welded into `paginationt-small`. It is now the engine's
own markup — newer, a position, older — which also drops numbered pages, matching a decision
the engine recorded for itself: deep page numbers are URLs a crawler walks and a reader
does not use.

Two smaller things came out with them. The archive heading was being set in the reading face,
where the engine's sheet says in as many words that an archive heading is chrome and stays in
the chrome face — it is furniture, not the reader's words. And a search that matched nothing
was answered with "No posts here yet", which tells a reader the whole blog is empty; a failed
search and an empty archive are not the same sentence and no longer share one.

[`check:classes`](../tools/checks/dead-classes.ts) is the guard that would have caught all of
it: every class a template prints must reach a rule, or be named in an allowlist with a
reason. Sixteen are named there — core's own markup, and the two that
[`quireink_align_classes`](../quire-ink/inc/template-tags.php) rewrites at render time.

**Pictures, every way a block can place one.** The block sampler holds no media, because the
theme bundles no images and a fixture that needs an upload is a fixture nobody runs. The
seeder DRAWS two now, with GD, and places them every way a reader meets: column width with a
caption, wide, full, a left float with text beside it, a gallery, media-and-text, a cover, and
a featured image. Nothing was wrong. Two things looked wrong and were not, which is why they
are written down:

* **`alignfull` renders at the same width as `alignwide`**, on purpose and with the reason in
  `quireink_align_classes`: a band edge to edge across a reading column is a shape the engine
  measured and declined. It surprises an author who picked "Full", so `readme.txt` now says it
  rather than leaving it to be discovered.
* **A gallery after a left-floated picture is squeezed beside it.** A grid container
  establishes its own formatting context and a formatting context avoids floats; measured at
  388px of a 608px column, and 608px the moment the float above is cleared. CSS doing what it
  says, and a fixture with too little text beside the float.

Both featured-image shapes were switched on and looked at for the first time: the article hero
above the title at 3:2, and the listing thumbnail as a 96px square the words wrap around,
which is the rule the engine states.

**Print was printed, not read.** `--print-to-pdf` on an article, converted and looked at: the
masthead earns its ink once as provenance with a rule under it, then the meta line, the title
and the article. Every link prints its own address after it in grey. The rail, the header
controls, the conversation and the footer are gone. Twenty rules, all of them the engine's.

**Every archive shape answers.** Author, month, year, category and tag all return 200 with the
heading `the_archive_title()` gives them, and the feed with them.

**The search overlay could never have returned anything.** `[/find]` in the header, and the
`/` key anywhere on the page, open an overlay that searches as you type. It is in the copied
reader bundle, and it fetches `/api/search?q=`, which is the blog engine's route. WordPress
does not have it. `if (!res.ok) return` is the bundle's whole error handling, so the overlay
opened, took focus, and sat saying "Type to search posts." for as long as anyone typed, on
every page, since the first day.

It shipped that way for the same reason book mode did: until the origin bug was found, no
render in this project had ever run JavaScript. The bundle may not be edited, so the route is
answered instead of moved — `inc/search-api.php`, on `parse_request`, eight posts, and the
shape read off the bundle rather than guessed. `slug` carries the whole path under the site
root rather than the post's slug, because the bundle prefixes one slash and a real slug would
404 on any site whose permalinks are not `/%postname%/`.

**Two things in the comment thread that a click found and no reading would have.**

* **The reply form was landing outside the comment**, as a `<div>` that is a direct child of
  a `<ul>` — which no list may contain — because `add_below` named the `<li>` itself. So the
  sheet's own rule for a reply form, written to strip the card's border because a second
  bordered box inside a thread boxes a box, reached nothing: replying opened a full card in
  the middle of the conversation. The anchor is the comment body now, which is inside the item.
* **The Reply link's class alias had never once applied.** The filter looked for
  `class='comment-reply-link` with a single quote and WordPress writes a double one, so
  `.comment-reply` — its meta colour, its top margin, its hover — reached nothing on every
  thread since the day it was written. A filter that changes nothing returns the string it
  was given, so there was nothing to see. `check:classes` cannot catch this: it reads classes
  templates PRINT, and this one is added at run time.

**The drop cap was core's, three times over.** Recorded here a day earlier as "looked wrong
and measured fine", on the strength of one measurement that asked the wrong question: whether
it collided with the block under it. It does not collide. It is `font-size:8.4em` at
`font-weight:100`, which against this theme's 18.4px paragraph is a **151px letter spanning
3.85 body lines**, set in a weight none of the bundled faces carry. The engine states its own
at `3.1em`, weight 600, in the heading's ink - a third the size and a different animal.

The fix is generated rather than written, because the numbers are the engine's and this theme
may not type them out. `tools/appearance-php.ts` reads the rule out of the engine's own
`BOOK_CSS`, where it is stated once for book mode, and re-addresses it at the class Gutenberg
puts on the paragraph. It throws rather than guesses if that rule ever moves, and
`check:generated` goes red until an extract catches up.

**Three more surfaces nobody opens while writing a theme.** Ticking "password protected" or
splitting a post into pages is one click in the editor and there is no occasion to make it;
an attachment page needs an upload. All three are seeded now.

* **The password form arrived wearing nothing at all** - `get_the_password_form()` prints a
  bare label, an unclassed input and an unclassed submit, so a protected post showed a browser
  default box and a grey system button in the middle of a reading column. It wears `.subscribe`
  now, the engine's own name for a short form of one field and one button, which the newsletter
  box and the search page already use. The action, the field name and the `#pwbox-<id>` are
  WordPress's and cannot change, so anything looking for them still finds them.
* **The info column beside a protected post was reporting its word count and reading time**,
  which measure text the password is there to withhold. `quireink_reading()` declines to
  measure a protected post and the three places that print it skip the line rather than
  printing a zero.
* **Attachment pages are not a gap.** Since WordPress 6.4 core disables them
  (`wp_attachment_pages_enabled` is `0`) and redirects to the file, which is what a request
  for one does here: HTTP 200 at the image itself. No `attachment.php` is needed, and one
  would be dead code.

**A post in three pages works**, and the first test of it did not, which is worth recording
because the mistake looks exactly like a theme bug. `<!-- wp:nextpage /-->` is the block
delimiter; the page break WordPress splits on is the `<!--nextpage-->` INSIDE it. Written with
only the delimiter, a three-page post renders as one page with no links under it.

**A footer menu.** The one block on the page with no counterpart upstream: the blog engine's
footer is a single line and has never had a menu under it, so this is the one place the theme
states a layout rather than translating one, and it states as little as it can. Registering it
found a live defect in the theme's own filter: `nav_menu_link_attributes` fires for EVERY menu
on the page, so `quireink_nav_link_atts` was putting `rail-row` on the footer links - a
full-width row with a hover slab and an aria-current marker down its side. It tests the
location now.

**Every core block on one page, which nobody had ever inserted.** The pull quote taught this
lesson once and it was not learned generally: a block that has never been inserted is a block
nobody has ever looked at. One post now holds every core block the theme can render without a
media library, `dev/seed/every-block.html`, and the seeder keeps it. Three defects came out of
the first render:

* **The outline button arrived filled.** Core's `is-style-outline` puts its transparent
  background inside a `:where()`, which carries no specificity, so `bridge.css`'s accent fill
  outranked it and an author who chose "Outline" got the same solid button with a light rule
  around it. It reads as a mistake rather than a choice. Undoing the fill is enough, because
  core supplies the border.
* **The tag cloud's smallest link measured 10.67px**, against a theme whose smallest type is
  15px. `wp_tag_cloud` writes an inline `font-size` scaled by how many posts carry the tag,
  and a tag with one post is not less readable than a tag with forty. The sizes come off; what
  is left is the engine's own answer to a run of tags, described in its sheet as plain words
  with no chips and no boxes.
* **The search block's button was `#32373c`**, core's default, a colour in none of the six
  palettes. `theme.json` now carries `elements.button` in the language the theme's own buttons
  already speak: the page's ground, the heading's ink, a hairline. It deliberately does NOT
  reach the Button block, which `bridge.css` fills with the accent because a call to action is
  meant to be loud.

Two things looked wrong and measured fine, which is worth recording so nobody re-opens them.
The drop cap is core's rather than the theme's own, and it crowds the block under it, but the
overlap measures −25px: it does not collide. And `h4` sits at 18.4px against a body of 18px,
which makes a fourth-level heading nearly invisible — that is the blog engine's scale, and
this theme does not get to choose type sizes.

**On a virgin WordPress the rail empties itself correctly.** Checked the way the directory
checks it - a reset database, `wp core install`, the theme activated, nothing but Hello world!
and Sample Page - because that is the install a reviewer opens and it is not an install this
project ever had. Every rail block whose source is empty simply does not print: no menu, so no
menu rows; no sticky post, so no Featured; no tags, so no Tags. What is left is Categories and
Archive, each with a count of 1, and the page reads as a new blog rather than as a broken one.
The article page keeps its right-hand info column, and drops the table-of-contents rail
because "Hello world!" has no headings.

The one thing a reviewer sees there that this theme would rather they did not is the next
paragraph.

**The rail's hairline runs past the content on a short page**, several hundred pixels of it,
and that one is NOT a defect here: `main{flex:1}` and `.with-rail{position:relative;flex:1}`
are the engine's own rules, so the rail stretches to a row that a sticky footer has already
pushed to the bottom of the window. Quire Ink does the same thing; its pages are just rarely
three lines long, and a WordPress page can be. Answering it would mean this theme overruling
the engine's layout, which is the one thing it does not do.

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

**The post's own author is marked, in a word.** WordPress puts `bypostauthor` on the item and
the sheet draws nothing for it, so in a thread the writer's own reply looked exactly like a
stranger's. It says `author` in the meta line now, between the name and the date. Not a
colour: a colour would be this theme deciding something the engine has not, and a distinction
carried only by colour is not a distinction for every reader. It is set from the account, not
from the name typed into the form.

**`dev/seed.sh` creates the thread now**, three comments on the newest post - one that wraps,
one REPLY from the post's author, which is the only way `bypostauthor` ever fires, and a
one-liner with a URL on the name. Every comment defect found here was found by putting real
comments in front of the markup, and a fresh seed used to leave an empty thread, so the
surface was only ever checked when somebody remembered to fill it by hand. Re-running the
seeder leaves an existing thread alone.

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
