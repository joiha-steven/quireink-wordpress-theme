=== Quire Ink ===
Contributors: joihasteven
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: blog, one-column, two-columns, left-sidebar, custom-colors, custom-logo, custom-menu, featured-images, sticky-post, threaded-comments, theme-options, translation-ready, rtl-language-support, block-patterns, block-styles, editor-style, wide-blocks

The reading surface of the Quire Ink blog engine, as a WordPress theme.

== Description ==

A reading theme for people who write long things. Six palettes in light and dark, a
self-hosted type system with no external requests, a table-of-contents rail that stands in
the gutter beside the article, and a timeline down the side of the listing page marking each
year and month.

Everything visual in this theme is generated from the Quire Ink blog engine's own stylesheet,
rather than copied by hand, so what it renders is what that blog renders.

= What it does =

* Six palettes, light and dark, chosen by the reader and remembered.
* Self-hosted typefaces. No Google Fonts, no CDN, no request off your own domain.
* A table of contents in the gutter, built from the headings in the post, with scroll
  tracking.
* Book mode: the article reset as two columns, like a page.
* Book typography: indented paragraphs, justified lines, hyphenation. Off by default.
* A listing timeline: a spine in the gutter with a sticky year and a marker for each month.
* Shape controls that actually change the shape - density, corner radius, heading weight.
* A rail you can leave alone or take over: empty, it shows the site's own menu, featured
  posts, categories, archive and tags; add a widget and the widgets are the rail.
* Featured images, off by default, in two shapes that are not further choices.
* Print styles that print the article and not the furniture.

= What it deliberately does not do =

* No custom header or custom background image. The palettes are the background, and a header
  image would sit above a wordmark that is already the header.
* No pen strokes or syntax highlighting. Both exist in the Quire Ink blog engine and neither
  can be authored in the block editor, so neither is shipped.

== Installation ==

1. Appearance -> Themes -> Add New -> Upload Theme.
2. Activate.
3. Appearance -> Customize for the palette, shape and reading controls.
4. Appearance -> Menus, and assign a menu to the "Rail menu" location, for the sidebar.

Mark a post Sticky to have it appear under "Featured" in the rail.

== Frequently Asked Questions ==

= Where is the sidebar? =

In the left gutter, on screens wide enough to hold one beside a centred reading column. Below
that width it is the menu button in the header. On a single post the gutter holds the
article's table of contents instead: the sheet lays out for one rail, and two would be a
column of links over a column of links.

= Why is the reading column so narrow? =

Because a line of about 70 characters is easier to read than a line of 120. The width is
`--shell-w` and custom CSS can change it.

= Can I turn the footer credit off? =

Appearance -> Customize -> Quire Ink - footer.

== Copyright ==

Quire Ink WordPress theme, Copyright 2026 Tran Manh Hung.
Quire Ink is distributed under the terms of the GNU GPL v2 or later.

This program is free software: you can redistribute it and/or modify it under the terms of
the GNU General Public License as published by the Free Software Foundation, either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See
the GNU General Public License for more details.

= Bundled typefaces =

All six are subset to Latin, Latin Extended and Vietnamese, and all six are SIL Open Font
License 1.1, which is GPL-compatible.

* Inter - Copyright (c) 2016 The Inter Project Authors
  Source: https://github.com/rsms/inter
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* Literata - Copyright 2018 The Literata Project Authors
  Source: https://github.com/googlefonts/literata
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* Source Sans 3 - Copyright 2010-2024 Adobe (https://adobe.com/)
  Source: https://github.com/adobe-fonts/source-sans
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* Source Serif 4 - Copyright 2014-2024 Adobe (https://adobe.com/)
  Source: https://github.com/adobe-fonts/source-serif
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* JetBrains Mono - Copyright 2020 The JetBrains Mono Project Authors
  Source: https://github.com/JetBrains/JetBrainsMono
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* IBM Plex Mono - Copyright 2017 IBM Corp.
  Source: https://github.com/IBM/plex
  License: SIL Open Font License 1.1, https://openfontlicense.org/

= Bundled scripts =

* assets/js/core.js, assets/js/post.js - Copyright 2026 Quire Ink contributors,
  https://github.com/joiha-steven/quireink. Included in this theme under the GNU GPL v2 or
  later, by the copyright holder.

= Screenshot =

The screenshot is a render of this theme, produced from the theme's own files. It contains no
third-party images.

== Changelog ==

= 0.1.0 =
* First version.
