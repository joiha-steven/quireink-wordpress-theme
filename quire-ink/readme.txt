=== Quire Ink ===
Contributors: joihasteven
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.4
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: accessibility-ready, blog, one-column, two-columns, left-sidebar, custom-colors, custom-logo, custom-menu, featured-images, sticky-post, threaded-comments, theme-options, translation-ready, rtl-language-support, block-patterns, block-styles, editor-style, wide-blocks

A minimal theme for people who write long things: a serif reading column, a contents rail, six palettes, self-hosted type, no tracking.

== Description ==

A minimal theme for long-form writing - an essay, a report, a piece somebody sat down to
write. A reader opens your post and gets a single column of about seventy characters a line,
set in a serif made for reading, with the article's own table of contents standing in the
gutter beside it and its word count and reading time in the other. They can pick one of six
palettes, in light or dark, and the site remembers it. If they would rather read it like a
page, book mode sets the article in two columns with a drop cap.

Nothing on the page comes from anywhere but your own domain. The typography is self-hosted -
seven typefaces inside the theme - so there is no Google Fonts, no CDN, no analytics, no
avatars, no tracking of any kind and no request off your server at all.

The look is not hand-written. It is generated from the stylesheet of the Quire Ink blog
engine, the same sheet that blog renders with, so this is that reading surface driven from
WordPress content rather than an impression of it. The engine is at
https://quireink.com and https://github.com/joiha-steven/quireink; this theme is at
https://github.com/joiha-steven/quireink-wordpress-theme, where its own documents live.

= Reading =

* Six palettes - Mono, Sepia, Forest, Ocean, Sci-fi, Amber - each in light and dark, chosen
  by the reader and remembered on their own device.
* Seven self-hosted typefaces, all SIL Open Font License, subset to Latin, Latin Extended and
  Vietnamese.
* Search as you type, from `[/find]` in the header or the `/` key anywhere on the page.
* A table of contents in the gutter, built from the post's own headings, tracking the scroll.
* Book mode: the article reset in two columns with a drop cap, like a page. On by
  default, and switchable off - which stops the 7.7 KB behind it being downloaded at all.
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
* **reading** - book mode, book typography, and whether motion is used at all.
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

* First visit: about 121 KB, of which 67 KB is type and 26 KB is the stylesheet.
* Every visit after that: about 17 KB. The rest is cached.
* WordPress's own emoji script and per-block styles are another 9 KB. Those are core's, not
  the theme's, and are not counted above.

Twenty-two font files ship and a browser fetches four of them, because each face declares the
range of characters it covers and the browser takes only what the page needs.

= Accessibility =

Text, headings, captions and links clear WCAG AA against their own background in all six
palettes and both schemes, the tightest measuring 5.05:1 against a floor of 4.5:1. There is a
skip link, one focus treatment for the whole site, no heading level skipped, a label on every
field, and links in running text are underlined rather than distinguished by colour alone.

The `accessibility-ready` tag **is** declared, as of 0.1.4. The one criterion that had kept it
off was the hairline around a form field: 1.26:1 against the page, where WCAG 2.1 asks 3:1 for
a control's boundary. Anything you type into or press now takes the same colour as a date or a
caption, 5.10:1 on the default palette and never under 5.05 on any of the six. The hairline
between two cards is unchanged, because a divider is decorative and a control's edge is not.

= Right to left =

An RTL locale gets a mirrored stylesheet, which WordPress links by itself. Tested by
installing Arabic and measuring both gutters.

= What it deliberately does not do =

* **No Site Editor.** This is a classic theme. Everything is in the Customizer, and the six
  panels above are the whole of it.
* **No custom header or background image.** The palettes are the background, and a header
  image would sit above a wordmark that is already the header.
* **Full-width alignment renders as wide.** A band running edge to edge across a reading
  column is a shape the blog engine measured and declined, so a picture set to Full gets the
  same treatment as Wide: it noses out into the gutter, and stops there.
* **No pen strokes and no syntax highlighting.** Both exist in the Quire Ink blog engine and
  neither can be written in the block editor, so neither is shipped. Half a feature is worse
  than none.
* **Nothing of yours is locked in.** No custom post types, no custom taxonomies, no database
  tables, no shortcodes. Switch away and every post is still a post.

== Installation ==

1. Appearance -> Themes -> Add New -> Upload Theme, then Activate.
2. Appearance -> Customize, for the palette, the shape and the reading controls.
3. Appearance -> Menus. A menu in the "Rail menu" location becomes the sidebar; one in
   "Footer menu" becomes a flat row of links above the credit line.

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

All seven are SIL Open Font License 1.1, which is GPL-compatible, and the six text faces are
subset to Latin, Latin Extended and Vietnamese. The full licence text, with every copyright
notice below repeated in it, ships as assets/fonts/OFL.txt. Each line is read out of the font
file's own name table rather than off a web page.

* Inter - Copyright 2016 The Inter Project Authors
  Source: https://github.com/rsms/inter
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* Literata - Copyright 2017 The Literata Project Authors
  Source: https://github.com/googlefonts/literata
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* Source Sans 3 - Copyright 2023 Adobe (https://adobe.com/), with Reserved Font Name 'Source'
  Source: https://github.com/adobe-fonts/source-sans
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* Source Serif 4 - Copyright 2014-2021 Adobe (https://adobe.com/), with Reserved Font Name 'Source'
  Source: https://github.com/adobe-fonts/source-serif
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* JetBrains Mono - Copyright 2020 The JetBrains Mono Project Authors
  Source: https://github.com/JetBrains/JetBrainsMono
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* IBM Plex Mono - Copyright 2017 IBM Corp., with Reserved Font Name 'Plex'
  Source: https://github.com/IBM/plex
  License: SIL Open Font License 1.1, https://openfontlicense.org/

* Kalam - Copyright (c) 2014 Indian Type Foundry (info@indiantypefoundry.com)
  Source: https://github.com/itfoundry/kalam
  License: SIL Open Font License 1.1, https://openfontlicense.org/
  Ten digits and a full stop only, 1.4 KB: the handwritten numerals on an ordered list.

= Bundled scripts =

* assets/js/core.js, assets/js/post.js - Copyright 2026 Quire Ink contributors,
  https://github.com/joiha-steven/quireink. Included in this theme under the GNU GPL v2 or
  later, by the copyright holder.

= Screenshot =

screenshot.png is a render of this theme on a WordPress install seeded for the purpose. The
words in it were written for the screenshot. It contains no third-party images and no
photographs.

== Changelog ==

= 0.1.4 =
Four defects a browser's network panel found and no amount of reading had, plus the first
re-generation from the blog engine in three weeks.

What this release still does NOT do. The rail's drawer is kept out of a phone's tab order by
JavaScript, so with scripting off its links are focusable and the menu button cannot open it;
the breakpoint that decides which it is comes from the blog engine and cannot be named in a
media query here. The rail renders a menu as a flat list. And the theme is still a classic
theme: there is no Site Editor.

* **The fonts' licence ships with the fonts again.** The SIL Open Font License asks that its
  text travel with the font software; `assets/fonts/OFL.txt` has been missing since 0.1.0,
  because the extractor swept the whole fonts directory before copying the faces in and took
  the licence with it. Kalam - ten handwritten digits used on an ordered list - was also not
  credited anywhere. Seven faces, seven copyright notices, each one read out of that font
  file's own name table.
* **No analytics, now including from the browser.** The reader bundle copied from the blog
  engine carries that product's beacon, which is not gated on a setting and needs no
  interaction: every page view POSTed the path, the referring host and the device's touch
  support to /api/track, and every departure posted scroll depth, engaged time and bytes.
  WordPress has no such route, so it was a 404 on every page view of every site running this
  theme since 0.1.0, under a description whose first claim is that nothing is tracked. The
  call is refused now.
* **Book mode works again.** The blog engine moved it into a bundle of its own on 6 September
  and this theme did not follow, so the buttons printed on every article and a click did
  nothing. It ships as its own bundle with a switch of its own under Customize - Quire Ink -
  reading: off removes the buttons and stops the 7.7 KB being downloaded.
* **The theme's own script is no longer render-blocking.** It asks WordPress for `defer` and
  was not getting it, because a handle with an inline script attached in the "after" position
  is not eligible for a delayed strategy. Both of the theme's inline guards moved to "before".
* **A default install no longer downloads a typeface it had switched off.** The furniture
  face is JetBrains Mono here and the blog engine's own default moved to Inter, so
  `--font-sans` was left at Inter on every install that had changed nothing. The page looked
  right - the rules that matter name the face explicitly - and the key on a code block pulled
  32.5 KB of Inter to set the word "Copy". Four font files reach a reader now, which is the
  number the description has claimed all along.
* **The stylesheet is 46 KB smaller over the wire**, and nothing was removed from it. The
  theme had been shipping the blog engine's stylesheet SOURCE - 198 KB of it, two thirds
  comments written for whoever next opens that file - where the blog itself serves the same
  sheet minified at 69 KB. The extractor runs the engine's own minifier now, so what this
  theme ships is what that blog ships. A first visit went from about 170 KB to about 121 KB.
* **The two faces the first screenful needs are preloaded**, so the first paint is the real
  type rather than a fallback that swaps.
* **Starter content**, so a brand-new blog has a rail menu to show rather than an empty
  gutter, and **five filters** for the decisions a theme has to guess at: what to read next,
  what counts as related, how many terms the rail lists, how many sticky posts are featured,
  and words a minute.
* Re-generated from Quire Ink after three weeks: two scroll-driven fades stop painting their
  own fill-mode over the page, two status messages stay in the accessibility tree instead of
  being display:none, and five controls that answered a pointer and then took a click in
  silence now press like the rest.
* Tested on WordPress 7.1.1.

= 0.1.3 =
Two findings from the second pass of the WordPress.org theme review, ticket 288845.

What this release still does NOT do. It is a beta, and it has still only been run on the
one site it was written against. The rail renders a menu as a flat list, so every level
is displayed and reachable but the depth is not in the markup and a screen reader hears
one run of links. Keeping the closed drawer out of the tab order is JavaScript, so with
scripting off it is focusable as before. And `accessibility-ready` is still not declared:
a form field's hairline measures 1.16:1 against the page where WCAG 2.1 asks 3:1, and
that colour belongs to the blog engine this theme is generated from.

* No direct database queries. The rail's archive block counted posts per year with SQL of
  its own; it asks `wp_get_archives()` now, which is core's answer to the same question
  and caches it, which the hand-written query never did.
* A menu assigned to the Rail menu location is reachable on an article. The rail stands
  down on a post so the gutter can hold that article's table of contents, but the contents
  need two headings, and below that floor there was no rail at all - and with none in the
  document the theme's own script hid the header's menu button. So on a post with one
  heading or none the menu was not in the gutter, not in the drawer, and had no control to
  open either, at any width. The gutter now carries the menu under the contents, and
  carries it alone when there is nothing to index.
* A menu with child items renders as one flat list rather than a nested one. Every level
  shows. Nesting met three rules this column was not written for: a sub-menu's first row
  is also a `:first-child`, so it sat 24px under its parent where every other row sat at
  32px; the row numbering restarts on each list, so three items with two children read
  1, 2, 1; and an indent has to pick a side, while this rail ranges left as a drawer and
  right in the desktop gutter.

= 0.1.2 =
Five findings from the WordPress.org theme review, ticket 288845. Four were code; the
fifth asked for a copyright and licence line the readme already carried.

What this release still does NOT do. It is a beta, and it has still only been run on the
one site it was written against. Keeping the closed drawer out of the tab order is
JavaScript, so with scripting off it is focusable exactly as it was. And
`accessibility-ready` is still not declared: a form field's hairline measures 1.16:1
against the page where WCAG 2.1 asks 3:1, that colour belongs to the blog engine this
theme is generated from, and it is answered there rather than overridden here.

* The sidebar is reached by keyboard where a reader sees it: in the gutter beside the
  first line, not after every link on the page. It was printed from the footer, so on a
  listing page it came eight article links late.
* The off-canvas drawer is out of the tab order while it is closed. It sits off the left
  edge of the screen and was still focusable, which is eight invisible stops before the
  article on a phone.
* A menu with child items shows them. Both menu locations rendered their top level and
  dropped every level under it. In the rail a child is indented on both sides, because
  the rail ranges left as a drawer and right in the desktop gutter.
* The "Skip to content" link is no longer drawn behind the WordPress admin bar, which is
  fixed to the top of the viewport for a logged-in reader and covered it on every page.
* A long unbroken title no longer gives the page a horizontal scrollbar. An 87-character
  title measured 1905px of document against a 1440px viewport.

= 0.1.1 =
Still a beta: the theme has not been run on a site other than the one it was written
against.

* Search as you type works. It never had: the overlay fetches a route the blog engine
  serves and WordPress does not, so it opened, took focus, and answered nothing, on every
  page. The route is answered now.
* Book mode works. It never had: it opens from a button, the script that builds it was
  being refused by the browser in every render this theme had ever been looked at, and the
  button did nothing. The theme was advertising it on the fact that the button existed.
* The drop cap is the theme's own. Core's is 8.4em at weight 100, which at this theme's
  paragraph is a 151px letter across nearly four lines, drawn in a weight the bundled
  typefaces do not carry.
* The Outline button style no longer arrives filled.
* The tag cloud block no longer prints links at 10.67px, under the theme's smallest size.
* The search block's button is the theme's colour rather than core's #32373c.
* The password form is no longer a browser default box and a grey system button, and a
  protected post no longer reports the word count and reading time of the text it withholds.
* Replying to a comment opens the form inside the comment rather than beside it, where the
  markup was also invalid, and the Reply link finally takes the theme's own styling: the
  filter that gives it that class had looked for the wrong quote mark and had never once
  matched.
* A second menu location, Footer menu, for a flat row of links above the credit.
* A post whose author was deleted no longer prints two separators in a row.
* `Tested up to` reads 7.1, from the install rather than from a filename.
* readme rewritten, and the screenshot rebuilt from a WordPress seeded for the picture
  instead of from one person's blog.

= 0.1.0 =
* First version.
