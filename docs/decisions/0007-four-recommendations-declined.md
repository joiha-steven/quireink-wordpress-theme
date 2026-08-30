# 0007 — Four Theme Check recommendations, declined

**In force.**

Theme Check runs clean of REQUIRED and WARNING findings. Four RECOMMENDED items remain and
all four are answered here, so nobody has to work out whether they were missed.

## No widget areas

`register_sidebar` is not called.

The rail IS the sidebar, and what goes in it is the site's own structure: its menu, its
featured posts, its categories, its archive, its tags — rendered from what WordPress already
knows, in the blog engine's own order. A widget area would let anything at all into that
gutter, and the gutter is a third of the design. A calendar widget and a tag cloud in a
250px column ranged right against a hairline is not the same page.

The escape hatch exists and is documented: custom CSS can reach every class name in the rail,
and a child theme can replace `parts/rail-blocks.php` outright.

## No custom header image

The header is a wordmark and three controls on one line. An image above it would be a second
header, and the theme already accepts a logo of any shape through `custom-logo`.

## No custom background

The background is the palette — six of them, light and dark, switched by the reader. A
background image or a colour picker underneath that is a seventh answer that fights the other
six, and the first thing it breaks is dark mode.

## "This theme doesn't seem to display tags"

It does. `quireink_term_line()` prints them twice on an article (the meta line and the desktop
info column), `quireink_rail_terms()` prints the tag cloud in the rail, and `post_class()`
carries them on every row of every listing.

Theme Check looks for a literal `the_tags(` call. The theme uses `get_the_terms()` because it
builds its own markup — `.term-list`, `.link-accent`, `.lower` — and `the_tags()` cannot
produce that. Reaching for `the_tags()` to satisfy a grep would mean rebuilding the markup
around what a helper happens to emit.

If a reviewer raises it, this file is the answer.
