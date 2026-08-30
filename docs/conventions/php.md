# PHP

## Escaping

Every value printed into a page is escaped at the point of printing: `esc_html`, `esc_attr`,
`esc_url`, or `wp_kses_post` for markup that is meant to be markup. This is
[invariant 4](../invariants.md) and `check:escape` enforces it.

The check is blunt on purpose and can be wrong. The escape hatch is a `phpcs:ignore` comment
on the line, and it must say why. Two lines carry one today; both print a string that was
escaped piece by piece a few lines above.

## Prefixes

Everything global is `quireink_`: functions, theme mods, handles, filters. A theme shares a
namespace with every plugin on the site, and WordPress.org rejects a submission that does not.

## The 400-line ceiling

Borrowed from the blog engine, enforced by `check:filesize`, with a warning band from 380.
Generated files and Quire Ink's copied JS bundles are exempt — nobody reads them and nobody
edits them.

Splitting is cheap when you choose the moment and expensive when the build chooses it for you,
which is the whole reason for the warning band.

## Templates hold Quire Ink's markup, element for element

The stylesheet binds to exact names: `div.wrap`, `.with-rail`, `main#content`, `article`,
`aside.post-info`, `nav.toc.rail`, `#post-body.prose`. Where WordPress wants a hook, the hook
goes in. Where it wants a different shape, the shape here wins.

Two consequences that look like bugs and are not:

**The byline is printed twice.** `.post-meta` is hidden on desktop — the facts move to the
right-hand column there — so a byline printed only in the meta line is invisible on every
desktop screen. Quire Ink shipped exactly that, and it was found by opening the page.

**The content is rendered before anything is printed.** `quireink_anchor_headings()` is a
`the_content` filter that gives every heading an id and collects them for the rail on the same
pass. The rail is printed ABOVE the article, so `the_content()` has to have run first:
`$rendered = apply_filters( 'the_content', get_the_content() )` at the top of the loop. Calling
`the_content()` in place leaves the rail permanently empty, and an empty rail also silently
disables the desktop three-column layout, because the sheet keys that off a `.rail` sibling
being present.

## One exception to "never hand-copy"

`quireink_shape_css()` in `inc/customizer.php` holds three tables copied from the blog engine's
`content/settings-shape.ts`, because a Customizer control cannot read TypeScript. They are
three lines each and they are marked. Everything else comes through
[the extractor](extract.md).
