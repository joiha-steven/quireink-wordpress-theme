# Map — a symptom, and the files to open first

Not loaded automatically; `CLAUDE.md` points here. Open it when you do not know where a change belongs.

| Symptom / area | Read these first |
|---|---|
| A colour, size or breakpoint is wrong | `tools/extract.ts`, then the blog engine's `src/web/*.css.ts` — never edit the generated CSS |
| A Gutenberg block looks wrong | `quire-ink/assets/css/bridge.css`, `quire-ink/inc/template-tags.php` (`quireink_align_classes`) |
| The rail, the table of contents, the desktop three-column layout | `quire-ink/functions.php` (`quireink_anchor_headings`, `quireink_toc`), `quire-ink/single.php` |
| An article's furniture — byline, word count, terms | `quire-ink/single.php`, `quire-ink/inc/template-tags.php` |
| The listing page | `quire-ink/index.php`, `quireink_list_row()` |
| A block looks unstyled although its class is spelled right | `bun run check:classes` first — the sheet usually has the block under the blog engine's own name (`.listing-head`, `.pager`, `.empty`) |
| A knob in the Customizer | `quire-ink/inc/customizer.php` |
| Strings the reader JS puts on screen | `quire-ink/inc/i18n-data.php` — they are `data-` attributes on `<body>`, read at run time |
| The local WordPress | `dev/docker-compose.yml`, `dev/up.sh` |
| An imported article looks wrong | `dev/seed/fetch.py` |
