# Quire Ink for WordPress

The reading surface of [Quire Ink](https://quireink.com) as a WordPress theme. `quire-ink/` is the
theme; everything else exists to generate it, run it, or explain it.

**Not licensed for release.** [ADR 0005](./docs/decisions/0005-licence-not-decided.md) is open
and the answer cannot be undone. Do not make this repository public without settling it.

## Verify

```
bun run check:all
```

Seven static guards — `filesize` · `order` · `bridge` · `contrast` · `escape` · `generated` · `docs`. Seconds.
`check:generated` skips with a warning when there is no Quire Ink checkout beside this one.

`check:all` proves the seams hold. It cannot tell you the rail is empty, a figure is at the
wrong width, or every headline on the listing page has picked up a link underline. All three
happened here, all three passed every check, and all three were obvious in a screenshot.

```
dev/up.sh        # WordPress on http://localhost:8099, admin / admin
dev/seed.sh      # real articles from the live blog, converted to Gutenberg blocks
tools/shot.sh <url> .tmp/shots/<name>.png
```

**Open the page.**

## Read first

| Doing | Read |
|---|---|
| Anything at all | [`docs/invariants.md`](./docs/invariants.md) — the 6 load-bearing rules |
| Finding your way | [`docs/README.md`](./docs/README.md) — the index |
| Adding or changing a setting | [`docs/appearance.md`](./docs/appearance.md) — every knob, and what cannot be changed. Update it in the SAME commit |
| Touching CSS | [`docs/conventions/css.md`](./docs/conventions/css.md) |
| Touching a template | [`docs/conventions/php.md`](./docs/conventions/php.md) |
| Taking something new from the blog engine | [`docs/conventions/extract.md`](./docs/conventions/extract.md) |
| Going against a past decision | [`docs/decisions/`](./docs/decisions/README.md) — the in-force index first |
| Wondering what does not survive the trip | [`docs/gaps.md`](./docs/gaps.md) |
| Touching a control, a colour or a heading | [`docs/accessibility.md`](./docs/accessibility.md) — the measurements, and the one criterion that fails |

## Debug router — a symptom, and the files to open first

| Symptom / area | Read these first |
|---|---|
| A colour, size or breakpoint is wrong | `tools/extract.ts`, then the blog engine's `src/web/*.css.ts` — never edit the generated CSS |
| A Gutenberg block looks wrong | `quire-ink/assets/css/bridge.css`, `quire-ink/inc/template-tags.php` (`quireink_align_classes`) |
| The rail, the table of contents, the desktop three-column layout | `quire-ink/functions.php` (`quireink_anchor_headings`, `quireink_toc`), `quire-ink/single.php` |
| An article's furniture — byline, word count, terms | `quire-ink/single.php`, `quire-ink/inc/template-tags.php` |
| The listing page | `quire-ink/index.php`, `quireink_list_row()` |
| A knob in the Customizer | `quire-ink/inc/customizer.php` |
| Strings the reader JS puts on screen | `quire-ink/inc/i18n-data.php` — they are `data-` attributes on `<body>`, read at run time |
| The local WordPress | `dev/docker-compose.yml`, `dev/up.sh` |
| An imported article looks wrong | `dev/seed/fetch.py` |

## Hard rules — each one is a bug that already shipped

- **Quire Ink is READ ONLY.** `../quireink` is a released product with production instances.
  Read it with ABSOLUTE paths and never `cd` into it: the shell keeps its working directory
  between commands, so a later write lands in the wrong repository. Six files went in that
  way, and the symptom lies — `php -l` passes on the file it just wrote, `ls` from here says
  the file does not exist, and you go looking for a disk problem.
- **Never hand-copy a value out of the blog engine.** It comes through `tools/extract.ts` or
  it does not come. One marked exception: the three shape tables in `inc/customizer.php`.
- **`bridge.css` translates, it never decides.** No hex, no colour function, no length that is
  not `0`/`1px`/`2px`/`100%`. A value that is not already a Quire Ink variable belongs
  upstream. Prefer aliasing a class NAME in PHP over copying a RULE in CSS.
- **The sheets load base → tokens → bridge, wired by dependency.** The generated half exists
  to win. [Invariant 1](./docs/invariants.md) explains what breaks silently when it does not.
- **Everything printed is escaped** at the point of printing. `phpcs:ignore` needs a reason.
- **Everything global is prefixed `quireink_`.** A theme shares a namespace with every plugin
  on the site.
- **Never quote the owner** — not in code, comments, docs, ADRs or commit messages. State the
  fact or the measurement.
- **No support for markup nobody can author.** [ADR 0003](./docs/decisions/0003-skip-what-gutenberg-cannot-express.md).
  Build the authoring side first, or ship neither.

## Danger zones

- **`quire-ink/assets/css/quireink-{base,tokens}.css`, `assets/fonts/`, `assets/js/` are
  GENERATED.** Editing them is not wrong so much as pointless: the next extract overwrites
  them and `check:generated` is red until it does.
- **The blog engine moves.** A red `check:generated` is the seam reporting, not a failure.
  Re-run the extractor and READ the diff before committing it.
- **`dev/` throws its database away.** `dev/down.sh` is `docker compose down -v`. Nothing in
  that stack is worth keeping, and a half-migrated database is the one way it could lie.
- **Port 8099, not 8088.** The jellykey-local PHP server binds `[::1]:8088`; Docker publishes
  on `0.0.0.0`, so nothing collides at bind time and the browser just resolves `localhost` to
  `::1` and shows the other site.
- **All scratch goes under `.tmp/`** — one gitignored root, never a new one.
