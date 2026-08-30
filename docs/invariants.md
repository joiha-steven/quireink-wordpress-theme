# Invariants

The load-bearing rules. Break one and nothing crashes — the page just quietly stops being the
thing it was copied from, and no test goes red.

Each is enforced in ONE place and pinned by a guard, all run by `bun run check:all`. A change
that weakens one updates its guard in the SAME commit, which is what makes the weakening
visible in review.

| # | Rule | Enforced at | Pinned by |
|---|---|---|---|
| 1 | **The sheets load base → tokens → bridge, by DEPENDENCY, on the page AND in the editor.** The generated half is generated so that it can win: `.rail` is a slide-out drawer in the static sheet and only the computed media query promotes it into the desktop gutter | [`quire-ink/functions.php`](../quire-ink/functions.php) `quireink_assets()` and `add_editor_style()` | `check:order` |
| 2 | **`bridge.css` translates, it never decides.** No hex, no colour function, no length that is not `0`/`1px`/`2px`/`100%`. A value that is not already a Quire Ink variable means the two surfaces have started to drift | [`quire-ink/assets/css/bridge.css`](../quire-ink/assets/css/bridge.css) | `check:bridge` |
| 3 | **Nothing is hand-copied out of the blog engine.** Colours, sizes, breakpoints and font stacks come through the extractor or they do not come. One exception, marked as such: the three shape tables in the Customizer, which cannot read TypeScript | [`tools/extract.ts`](../tools/extract.ts) | `check:generated` |
| 4 | **Every value printed into a page is escaped** at the point of printing | every template under [`quire-ink/`](../quire-ink) | `check:escape` |
| 5 | **Quire Ink is read only.** This repository imports its modules and writes nothing to it | [`tools/tsconfig.json`](../tools/tsconfig.json) | nothing yet — see below |

## Why 1 is the first one

It is the bug this repository exists to not repeat, and it is worth reading the shape of it.

Enqueued the intuitive way round — variables first, because everything reads them — the
static sheet's mobile drawer rule beats the generated desktop geometry on source order. The
rail then renders as a fixed, translated-off-screen drawer at every width. Nothing errors,
nothing logs, the HTML is correct, every element is present with the right class, and the
table of contents is simply never visible on a desktop screen. Three rounds of screenshots
went past it before anyone read the cascade.

`check:order` tests the dependency chain rather than the order of the calls, because
WordPress emits by dependency: two `wp_enqueue_style` calls in the right order with the wrong
`$deps` can still come out backwards.

**And the editor is the other half of the rule.** For a long time the guard read
`wp_enqueue_style` calls only, and on the other side of that blind spot `add_editor_style()`
was listing tokens BEFORE base — the exact inversion the check exists to prevent, three lines
above the calls it was checking. The editor has no rail, so it was not the same bug; it was
the same mistake. The guard reads both now.

One sheet is deliberately **off the chain**: `quireink-ide` is enqueued only when the
Customizer switch is on, and a conditional link cannot be a link in a chain. Where it lands
does not matter, and that is a property rather than a hope — every selector in it carries
`html[data-ide-chrome=on]`, so it cannot tie with anything else the theme loads. The guard
still requires it to depend on the base sheet, so WordPress can never emit it first.

## Why 5 has no guard

It is a rule about what this repository does to a DIFFERENT one, and a check that ran here
could only ever look at a tree it has no business touching. The protection is procedural and
written down in [`../CLAUDE.md`](../CLAUDE.md): read with absolute paths, never `cd` into the
sibling. It has been broken once — six files written into `../quireink/theme/` because a
`cd` from a previous command was still in effect — and the symptom lies, because `php -l`
passes on the file it just wrote while `ls` from here says the file does not exist.

A guard is possible (`git -C ../quireink status --porcelain` before and after) and is not
written, because the sibling has its own uncommitted work in it most of the time and the
check would cry wolf.
