=== Quire Ink ===
Contributors: joihasteven
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: blog, one-column, two-columns, left-sidebar, custom-colors, custom-logo, custom-menu, featured-images, sticky-post, threaded-comments, theme-options, translation-ready, rtl-language-support, block-patterns, block-styles, editor-style, wide-blocks

A theme for people who write long things and want them read.

== Description ==

A reader opens your post and gets a single column of about seventy characters a line, set in
a book face, with the article's own contents standing in the gutter beside it. They can pick
one of six palettes, in light or dark, and the site remembers it. If they would rather read
it like a page, book mode sets the article in two columns.

Nothing on the page comes from anywhere but your own domain. No Google Fonts, no CDN, no
analytics, no avatars, no request off your server at all.

The look is not hand-written. It is generated from the stylesheet of the Quire Ink blog
engine, the same sheet that blog renders with, so this is that reading surface driven from
WordPress content rather than an impression of it.

= Reading =

* Six palettes - Mono, Sepia, Forest, Ocean, Sci-fi, Amber - each in light and dark, chosen
  by the reader and remembered on their own device.
* Six self-hosted typefaces, all SIL Open Font License, subset to Latin, Latin Extended and
  Vietnamese.
* A table of contents in the gutter, built from the post's own headings, tracking the scroll.
* Book mode: the article reset in two columns, like a page.
* Book typography - indented paragraphs, justified lines, hyphenation - off by default,
  because it is a taste and not an improvement.
* A timeline down the listing page: a spine in the gutter, a sticky year, a marker at each
  new month.
* Print styles that print the article and leave the furniture out.

= Making it yours =

Six panels under Appearance -> Customize, all of them named "Quire Ink -".

* **shape** - density, corner radius and heading weight. These three are what make two Quire
  Ink sites look unlike each other.
* **colour** - the palette, and whether a first-time reader starts in light, dark, or
  whatever their system says.
* **type** - the reading face, the interface face, and a switch for the furniture that reads
  as source code.
* **pictures** - featured images in two shapes, off by default, and a frame around every
  figure on the site.
* **reading** - book typography, and whether motion is used at all.
* **footer** - the credit line, which you may turn off.

The rail in the left gutter shows the site's own structure: its menu, its sticky posts under
Featured, its categories, its archive and its tags, in that order. Put a widget in the Rail
sidebar and the widgets become the rail instead. Both are the rail; neither needs a plugin.

= Writing in it =

The block editor shows the article rather than a guess at it: the same reading face, the same
column width, the same code styling, so what you type is what publishes.

Eight patterns sit in their own inserter category, each one reaching something the sheet
already draws and the editor has no other way to ask for: Callout, Standfirst, Pull quote,
Wide picture, Two pictures side by side, Reference table, Code with a note, Further reading.

Four styles on the image block: Framed, Thin frame, Thick frame, Ink frame.

= What it costs to load =

Measured on one long article, gzipped as served, no plugins, the post's own pictures not
counted because those are your content and not the theme:

* First visit: about 152 KB, of which 68 KB is type.
* Every visit after that: about 18 KB. The rest is cached.

Twenty-one font files ship and a browser fetches four of them, because each face declares the
range of characters it covers and the browser takes only what the page needs.

= Accessibility =

Text, headings, captions and links clear WCAG AA against their own background in all six
palettes and both schemes, the tightest measuring 5.01:1 against a floor of 4.5:1. There is a
skip link, one focus treatment for the whole site, no heading level skipped, a label on every
field, and links in running text are underlined rather than distinguished by colour alone.

The `accessibility-ready` tag is **not** declared, for one measured reason: the hairline
around a form field is 1.16:1 against the page, where WCAG 2.1 asks 3:1 for a control's
boundary. That colour belongs to the blog engine this theme is generated from, so it is
answered there rather than overridden here.

= Right to left =

An RTL locale gets a mirrored stylesheet, which WordPress links by itself. Tested by
installing Arabic and measuring both gutters.

= What it deliberately does not do =

* **No Site Editor.** This is a classic theme. Everything is in the Customizer, and the six
  panels above are the whole of it.
* **No custom header or background image.** The palettes are the background, and a header
  image would sit above a wordmark that is already the header.
* **No pen strokes and no syntax highlighting.** Both exist in the Quire Ink blog engine and
  neither can be written in the block editor, so neither is shipped. Half a feature is worse
  than none.
* **Nothing of yours is locked in.** No custom post types, no custom taxonomies, no database
  tables, no shortcodes. Switch away and every post is still a post.

== Installation ==

1. Appearance -> Themes -> Add New -> Upload Theme, then Activate.
2. Appearance -> Customize, for the palette, the shape and the reading controls.
3. Appearance -> Menus, and assign a menu to the "Rail menu" location. That is the sidebar.

Mark a post Sticky and it appears under Featured in the rail. Give a post some headings and
its contents appear in the gutter.

== Frequently Asked Questions ==

= Where is the sidebar? =

In the left gutter, on a screen wide enough to hold one beside a centred reading column.
Narrower than that it becomes the menu button in the header. On a single post the gutter
holds the article's contents instead, because the sheet lays out for one rail and two would
be a column of links over a column of links.

= Why is the reading column so narrow? =

Because a line of about seventy characters is easier to read than a line of a hundred and
twenty. It is `--shell-w`, and custom CSS can change it.

= Can I use this with the Site Editor? =

No. It is a classic theme, so Appearance -> Customize is where the settings live and
Appearance -> Editor will not appear.

= Will my posts survive if I change themes? =

Yes. The theme registers no post type, no taxonomy, no table and no shortcode, and it stores
nothing in your posts. Every post is ordinary block content and stays that way.

= Does it phone home? =

No. There is no request to any host but your own: no fonts, no scripts, no analytics, no
avatars, no update check of its own.

= How do I turn the footer credit off? =

Appearance -> Customize -> Quire Ink - footer.

= Can I change something the Customizer does not offer? =

Every colour, size and spacing is a CSS custom property, and Additional CSS reaches all of
them. A child theme can replace any template outright, including the rail.

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
License 1.1, which is GPL-compatible. The full licence text ships as
assets/fonts/OFL.txt.

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

screenshot.png is a render of this theme on a WordPress install seeded for the purpose. The
words in it were written for the screenshot. It contains no third-party images and no
photographs.

== Changelog ==

= 0.1.0 =
* First version.
