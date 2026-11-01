# Quire Ink for WordPress

The reading surface of [Quire Ink](https://quireink.com) as a WordPress theme. `quire-ink/` is the
theme; everything else exists to generate it, run it, or explain it.

**GNU GPL v2 or later** ([ADR 0005](./docs/decisions/0005-gpl-v2-or-later.md)) — the licence
WordPress requires of a free theme, and the reason `bridge.css` may not invent a value: this
copy of the look is GPL, the blog engine is not.

## Verify

```
bun run check:all   # ten static guards, seconds; check:generated skips with a warning without a Quire Ink checkout beside this one
dev/up.sh           # WordPress on http://localhost:8099, admin / admin
dev/seed.sh         # real articles from the live blog, converted to Gutenberg blocks
tools/shot.sh <url> .tmp/shots/<name>.png
tools/kbd-probe.ts <url> [--width=] [--open-rail]   # tab order, region, on-screen or not, horizontal overflow, skip link vs admin bar
dev/screenshot.sh   # rebuilds quire-ink/screenshot.png from a WordPress seeded for it, then restores your database
```

`check:all` cannot see an empty rail, a figure at the wrong width or a link underline on every headline. **Open the page.**
`shot.sh` refuses to render below 500px (Chrome's headless minimum; use `kbd-probe.ts --width` there, phone widths are
photographed inside iframes) and refuses a page whose assets point at another origin: use `localhost`, never `127.0.0.1`.

## Read first

| Doing | Read |
|---|---|
| Anything at all | [`docs/invariants.md`](./docs/invariants.md) — the 7 load-bearing rules |
| Finding your way | [`docs/README.md`](./docs/README.md) — the index |
| Adding or changing a setting | [`docs/appearance.md`](./docs/appearance.md) — every knob, and what cannot be changed. Update it in the SAME commit |
| Touching CSS | [`docs/conventions/css.md`](./docs/conventions/css.md) |
| Touching a template | [`docs/conventions/php.md`](./docs/conventions/php.md) |
| Taking something new from the blog engine | [`docs/conventions/extract.md`](./docs/conventions/extract.md) |
| Anything that touches the plugin, or could | [`docs/pair.md`](./docs/pair.md) — generated; edit the master |
| Going against a past decision | [`docs/decisions/`](./docs/decisions/README.md) — the in-force index first |
| Wondering what does not survive the trip | [`docs/gaps.md`](./docs/gaps.md) |
| Touching a control, a colour or a heading | [`docs/accessibility.md`](./docs/accessibility.md) — the measurements, and the one criterion that fails |

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
- **Never require the Quire Ink Pen plugin, and never ship the pen's ink.** The two are made
  for each other and neither may need the other ([ADR 0010](./docs/decisions/0010-the-plugin-is-a-companion-not-a-dependency.md)).
  `docs/pair.md` is GENERATED from a master neither repository owns.

## Danger zones

- **`quire-ink/assets/css/quireink-{base,tokens}.css`, `assets/fonts/`, `assets/js/` are GENERATED.** Editing them is
  pointless: the next extract overwrites them and `check:generated` is red until it does.
- **The blog engine moves.** A red `check:generated` is the seam reporting, not a failure. Re-run the extractor and READ
  the diff before committing it.
- **`dev/` throws its database away.** `dev/down.sh` is `docker compose down -v`.
- **`localhost:8099`, never `127.0.0.1:8099`.** Under the wrong host CORS refuses every font and the type quietly falls
  back; `getComputedStyle` still answers `Literata`, so measure with `document.fonts`, not computed style.
- **Port 8099, not 8088.** jellykey-local binds `[::1]:8088`; the browser resolves `localhost` to `::1` and shows the other site.
- **All scratch goes under `.tmp/`** — one gitignored root, never a new one.

Find a file or a symptom: [`MAP.md`](./MAP.md). Why a rule exists: [`HISTORY.md`](./HISTORY.md).
