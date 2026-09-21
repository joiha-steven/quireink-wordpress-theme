# 0004 — English strings only

**In force.**

## Decision

The theme's own strings are English. The text domain (`quire-ink`, which is the slug, as
WordPress.org requires) and the escaping calls make it translation-ready, and no translation is
shipped.

## Why

A theme is read by whoever installs it, and English is the language a WordPress theme is
distributed in. Shipping one translation and not thirty raises the question of which language
is next; shipping none makes the answer "whichever one somebody contributes".

## What this looks like on a Vietnamese blog

The article furniture reads `2,738 words` / `14 min read` / `Tags` where Quire Ink reads
`2.799 chữ` / `14 phút đọc` / `Tag`. That is the theme's own furniture only — the writing, the
titles, the terms and the dates all come from WordPress and follow the site's own locale.

Anyone who wants it can drop a `quire-ink-vi.mo` into `wp-content/languages/themes/` without
touching this repository. It is 215 strings — most of them the Customizer's own labels and
descriptions, not the dozen words a reader sees.

**The channel for a translation is translate.wordpress.org, not this repository.** A theme in
the directory is translated there, by locale teams, and the result is delivered to every site
in that locale by WordPress itself. Putting a `.po` here would be a second copy that nobody
updates. *(This line said "about forty strings" from the day it was written, which was true of
a theme with four Customizer controls.)*

## Not affected

The Vietnamese font subsets stay. `unicode-range` means a browser downloads
`*-vietnamese.woff2` only when the page actually contains those codepoints, so they cost a
reader nothing and they are what makes the theme render a Vietnamese blog correctly. The
language of the interface and the coverage of the typeface are different questions.
