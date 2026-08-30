# House rules for this repository

## Quire Ink is READ ONLY

`../quireink` is a released product with production instances. This repository reads its
source and never writes to it — no files added, none changed, none removed. `tools/extract.ts`
imports its modules through `tools/tsconfig.json`, which maps `@/*` at that checkout; that is
the whole interface.

Read it with absolute paths. Do not `cd` into it: the shell keeps its working directory
between commands, and a later write lands in the wrong repository. That has happened, and the
symptom lies to you — `php -l` passes on the file it just wrote, and `ls` from here says the
file does not exist.

## Do not hand-copy values out of Quire Ink

Colours, sizes, breakpoints, font stacks: they come out through `tools/extract.ts` or they do
not come out. A number retyped here is a number that will disagree with the blog engine on
some later Tuesday and nobody will know which one is right.

There is exactly one exception, and it is marked as such in `theme/inc/customizer.php`: the
three shape tables, which have to exist in PHP because a Customizer control cannot read
TypeScript. They are three lines each.

## bridge.css is for translation, never for opinions

`theme/assets/css/bridge.css` exists to teach Quire Ink's sheet about `wp-block-*`. If a rule
there is really about how the site LOOKS, it belongs upstream in the blog engine, where the
extractor will bring it across on the next run. A value in that file that is not already a
Quire Ink variable is a bug.

## Stylesheet order is load-bearing

`quireink-base.css` → `quireink-ink.css` → `quireink-tokens.css` → `bridge.css`. That is the
blog engine's own order: it links the static sheet and inlines the generated half LAST,
because the generated half has to win. Enqueued the intuitive way round — variables first,
since everything reads them — the mobile drawer rule beats the generated desktop geometry and
the table of contents silently never appears on any desktop.

## Open the page

Three of the four defects found so far were invisible to every check that passed and obvious
in a screenshot: an empty rail, a figure at the wrong width, a paragraph indent that was a
setting rather than a bug. `tools/shot.sh` takes the picture; `dev/up.sh` runs the site.
