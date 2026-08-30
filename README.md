# Quire Ink for WordPress

The reading surface of [Quire Ink](https://quireink.com) as a WordPress theme: the same six
palettes, the same self-hosted type system, the same table-of-contents rail and book mode —
driven from WordPress content instead of Quire Ink's own.

**Status: private, unfinished, and not licensed for release yet.** See
[docs/decisions-pending.md](docs/decisions-pending.md) before this repository goes public;
one of the open items has to be settled first and it cannot be undone afterwards.

## The idea

Nothing in `theme/assets/css` is written by hand. `tools/extract.ts` imports Quire Ink's own
stylesheet emitters from a sibling checkout and runs them, so every colour, size and rule in
the theme is the value the live blog renders with — and when the blog's front end moves, the
theme is re-generated rather than re-read. The Quire Ink commit each build came from is
recorded in `tools/extract-manifest.json`.

Quire Ink itself is **read only** from here. This repository never writes to it.

```
theme/          the theme (slug: quireink)
  assets/css/   3 generated + 1 hand-written (bridge.css)
  assets/js/    Quire Ink's own reader bundles, copied
  assets/fonts/ 21 self-hosted woff2, all OFL
tools/          extract.ts (the generator), shot.sh (screenshots)
dev/            local WordPress in Docker, and a seeder that pulls real articles
docs/           what does not carry over, and what is still to decide
```

## Regenerate the look

Needs a Quire Ink checkout at `../quireink` and `bun`.

```bash
bun tools/extract.ts
```

## Look at it

Needs Docker.

```bash
dev/up.sh        # WordPress on http://localhost:8099, admin / admin
dev/seed.sh      # pull the articles in dev/seed/posts.txt off the live blog
dev/down.sh      # stop, and throw the database away
```

`dev/up.sh` is idempotent: run it again after editing the theme and it re-syncs without
reinstalling. The theme is bind-mounted, so an edit is live on the next request.

## Compare against the real thing

```bash
tools/shot.sh https://manhhung.me/<slug> .tmp/shots/quireink.png
tools/shot.sh http://127.0.0.1:8099/<slug>/ .tmp/shots/wordpress.png
```

Turn on **Appearance → Customize → Quire Ink — reading → Book typography** first if the blog
being compared against has it on; it is off by default, as it is in Quire Ink.
