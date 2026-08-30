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

## Not measured yet

**Speed.** Quire Ink serves about 114 KB for an article from a single process, with hashed
immutable assets and half the sheet inlined. This theme sits on WordPress: the base sheet is
135 KB uncompressed, plus 17 KB of tokens, plus whatever plugins the site has. Dropping the
pen took 273 KB off that, which was the largest single item.

A fair measurement needs a clean install and is worth doing before anyone claims a number.
