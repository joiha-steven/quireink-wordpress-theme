# Every knob, and what it cannot reach

The owner's map. Quire Ink keeps one of these and it is a promise to the person running the
site: if a control is here it exists, and if a thing is under "What cannot be changed" then
custom CSS is the answer and not a support ticket.

**Appearance → Customize.** Nine controls of the theme's own, in five sections, plus what
WordPress provides.

## Quire Ink — colour

| Control | Default | What it does |
|---|---|---|
| Palette | Mono | Which of the six a first-time visitor opens in. A reader who picks for themselves is remembered and always wins. |
| Offer *(palette)* to readers | all six on | One checkbox each. Clear a box and the switcher stops offering it. Below **two** offered, the switcher renders no control at all — a blog can be one palette and no widget. The site's own default is always offered, whatever its box says: a switcher that cannot get back to where the site started is a one-way door. |

The six are Mono, Sepia, Forest, Ocean, Sci-fi and Amber, each with a light and a dark half.

## Quire Ink — type

| Control | Default | What it does |
|---|---|---|
| Reading typeface | Literata | The words: articles, comments, list headlines. Choosing a face also loads **the type scale measured for it** — a serif runs smaller and wants tighter leading than a sans, so this is not a font swap, it is a reading setup. Literata, Source Serif 4, Inter, Source Sans 3. |
| Furniture typeface | JetBrains Mono | Dates, counts, the rail, the buttons. Not the words. JetBrains Mono, IBM Plex Mono, Inter, or the same face as the reading text. |

All self-hosted, all subset to Latin, Latin Extended and Vietnamese. No request leaves the
site's own domain.

## Quire Ink — shape

The three that actually tell two Quire Ink sites apart. Measured across three live blogs: with
84 colour fields and 27 typography numbers available, the entire visible difference between
them was two colour values nobody could see.

| Control | Default | What it does |
|---|---|---|
| Density | Normal | The spacing unit behind every gap in an article. Compact 0.82, Relaxed 1.22. |
| Corners | Soft | `0`, `.5rem` or `1rem`, everywhere a corner is rounded. |
| Heading weight | Normal | Moves the PAIR — a post title and a card title were never the same weight. Light 400/400, Normal 700/600, Bold 800/700. |

## Quire Ink — pictures

Both off. A blog that upgrades into this theme must not move a pixel until its owner moves one.

| Control | Default | What it does |
|---|---|---|
| Featured image on an article | None | Above the title, always 3:2. Above, because a picture under the headline pushes the first sentence off a phone. |
| Featured image in a list | None | A floated 96px square beside the words, or a 3:2 plate above them. **The shape is not a further choice**: a list of pictures has to look like a list, and three real files measured at ratios 0.70, 2.10 and 0.72 read as a tall block, a thin strip and a tall block. |
| Frame on every picture | None | A mat and a hairline around each picture in an article, the way a print is mounted — thin, medium or thick. This is the site-wide DEFAULT; a single picture overrides it from the block editor's Styles panel, including back to no frame at all. |
| Draw the frame in ink | Off | A modifier on whichever weight is chosen, not a fourth weight: the mat and the line in the heading colour instead of the page colour. Nothing to see until a frame is chosen. |

## Quire Ink — reading

| Control | Default | What it does |
|---|---|---|
| A first-time visitor opens in | Their device's setting | Or force light, or force dark. Decides the FIRST paint only; a reader's own choice always wins. |
| Book typography | Off | Indented paragraphs, justified lines, hyphenation — a printed page. Off is the web default: ragged right, a blank line between paragraphs. |
| Motion | On | Off removes every transition. A reader whose system asks for reduced motion gets that regardless. |
| Furniture reads as source code | On | The `//` before every small heading, the brackets around dates and counts, the line numbers down the sidebar, the numbering in a table of contents. One switch for all of it. It never reaches the article, the titles or the comments. |

## Quire Ink — footer

| Control | Default | What it does |
|---|---|---|
| Credit the theme | On | One `Quire Ink theme` link beside the copyright. |

## The rail — Appearance → Widgets

Leave the **Rail** area empty and the theme shows its own five blocks: the menu, featured
posts (sticky posts), categories with counts, the archive by year, and the tag cloud.

Put **one** widget in it and the widgets are the rail — all of it, not appended to the five.
A column where some blocks answer to the Customizer and some to the widget screen is two
mental models in one gutter.

Core widgets come out looking like rail blocks with nothing done to them, because a widget is
`<div><h2>Title</h2><ul>…</ul></div>` and so is a rail block. Nav Menu, Categories, Archives,
Recent Posts, Pages and Tag Cloud were all checked: headings, rows, counts in the right-hand
column and the numbered rings all land. A widget that ships its own stylesheet will look like
itself — the theme rewrites class names, it does not fight plugin CSS.
[ADR 0008](decisions/0008-the-rail-is-a-widget-area.md)

## What WordPress provides

**Site Identity** — the logo (any shape; it is not cropped), the site title, the tagline, the
icon. **Menus** — assign one to *Rail menu* and it becomes the first block in the sidebar.
**Homepage Settings**. **Additional CSS** — see below.

Mark a post **Sticky** and it appears under *Featured* in the rail, and takes an accent bullet
in the listing.

## What custom CSS may target

Additional CSS is the escape hatch and it is a real one. Every variable the theme reads:

* Colour — `--c-bg` `--c-text` `--c-heading` `--c-meta` `--c-link` `--c-accent` `--c-rule`
* Type — `--fs-*` `--lh-*` `--ls-*` for `h1 h2 h3 h4 h5 body small caption code`
* Space and shape — `--sp` `--density` `--radius` `--fw-title` `--fw-heading` `--shell-w`
* Faces — `--font-reading` `--font-sans` `--font-mono`
* The rail — `--rail-w` `--rail-gap` `--rail-pad` `--rail-top`

Stable class names: `.wrap` `.with-rail` `.rail` `.rail-inner` `.rail-row` `.rail-tags`
`.prose` `.post-list` `.reveal` `.post-info` `.post-meta` `.toc` `.tl-feed` `.tl-year`
`.tl-mark` `.card-thumb` `.post-hero` `.comment-form` `.comment-list` `.footer-text`.

`--shell-w` is the reading column. It is 672px and it is a variable precisely so a site that
wants a wider measure can say so in one line.

## What cannot be changed here

Not oversights — each is a decision with a file behind it.

* **Custom header image, custom background.** The palettes are the background.
  [ADR 0007](decisions/0007-four-recommendations-declined.md)
* **The pen, and syntax highlighting.** Quire Ink has both; the block editor cannot author
  either, so neither ships. [ADR 0003](decisions/0003-skip-what-gutenberg-cannot-express.md)
* **Per-token colour pickers, and the 27 typography numbers.** They exist in the blog engine
  and are deliberately not surfaced: they were measured to make no visible difference, and
  every one of them is reachable from Additional CSS by the variable names above.
* **The reading column ratio, the rail width, the breakpoints.** Computed from the column
  width by the blog engine's own code and brought across by `tools/extract.ts`. Changing one
  means changing it upstream and re-extracting, which is the point.
