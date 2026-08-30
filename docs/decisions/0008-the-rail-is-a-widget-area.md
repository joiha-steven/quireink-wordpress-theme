# 0008 — The rail is a widget area

**In force. Supersedes the "no widget areas" clause of
[ADR 0007](0007-four-recommendations-declined.md); the other three declines there stand.**

## Decision

`register_sidebar( 'rail' )`. Leave it empty and the theme shows its own five blocks — menu,
featured, categories, archive, tags. Put one widget in it and the widgets are the rail.

## Why the earlier answer was wrong

ADR 0007 refused a widget area on the grounds that "a gutter which accepts anything is a third
of the design gone". The reasoning sounded right and it missed the thing that decides it:

**WordPress's widget contract and a Quire Ink rail block are the same shape.** A widget is
`<div><h2>Title</h2><ul>…</ul></div>`. A rail block is `<div><h2>Title</h2><ul>…</ul></div>`.
And the sheet styles `.rail h2`, `.rail ul` and `.rail li` generically, without caring who
wrote them — so a core Categories widget dropped into that gutter is already a rail block
before anything is done to it.

What a widget does not bring is `.rail-row` on its links, `.rail-tags` on a tag cloud, and the
count in the column the sheet ranges right. `quireink_rail_widgets()` adds those on the way
out — the third use of the same aliasing move the alignments and the image frames already use,
and for the same reason: one definition of the look, upstream, rather than a second copy here.

The measurable result: four core widgets — Nav Menu, Categories, Archives, Tag Cloud — render
in the gutter indistinguishably from the blocks the theme writes itself, numbered rings and
all, with no CSS written for them.

## All or nothing, on purpose

One widget takes over the whole rail rather than appending to the built-in blocks. A column
where three blocks answer to the Customizer and two to the widget screen is two mental models
in one gutter, and nobody can predict the order they come out in.

## What the translation does, and what it will not do

Four passes, each undoing one difference:

1. `.tagcloud` becomes `.rail-tags lower` — a tag cloud is a flow of words, not a list.
2. The inline `font-size` core writes on every tag, sizing it by use from 8pt to 22pt, is
   removed. That is a second type scale arriving inside a design that has one. The count
   stays, for anyone who wants the information.
3. A list row's link becomes `.rail-row`, its text is wrapped so the flex row has something to
   range, and a trailing `(12)` becomes the `.rail-count` column.
4. Anything still unclassed gets `link-accent t-small`, MERGED into its existing class
   attribute. The first version prepended a second `class=` — of which a browser keeps the
   first and drops the rest. It rendered correctly and would have silently deleted whatever
   class the next plugin depended on.

It rewrites class names and nothing else. A widget that draws its own boxes, sets its own
colours or ships its own stylesheet will look like itself, in the gutter. That is the cost of
the escape hatch and it is the right cost: the alternative is a theme that overrides plugin
CSS, which is a fight nobody wins.
