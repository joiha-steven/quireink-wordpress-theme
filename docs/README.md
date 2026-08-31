# Documentation

| Doing | Read |
|---|---|
| Anything at all | [`invariants.md`](invariants.md) — the 7 load-bearing rules and what pins each |
| Adding or changing a setting | [`appearance.md`](appearance.md) — the OWNER's map of every knob, the variables custom CSS may target, and what cannot be changed. It is a promise to users: update it in the same commit |
| Touching anything a reader sees | [`conventions/`](conventions/README.md) — the three files below, and the rule underneath all of them |
| Touching CSS | [`conventions/css.md`](conventions/css.md) — which sheet a rule belongs in, and the order they load |
| Touching a template | [`conventions/php.md`](conventions/php.md) — escaping, prefixes, the file ceiling |
| Taking something new from the blog engine | [`conventions/extract.md`](conventions/extract.md) — how the generator works and what it refuses to do |
| Wondering why something is the way it is | [`decisions/`](decisions/README.md) — read the in-force index first |
| Wondering what does not survive the trip | [`gaps.md`](gaps.md) — measured, not guessed |
| Touching a control, a colour or a heading | [`accessibility.md`](accessibility.md) — what was measured, and the one criterion that fails |
| Getting this ready to publish | [`release-checklist.md`](release-checklist.md) — what is done, and the three things only the owner can do |

## Verify

```
bun run check:all
```

Eight static guards: `filesize` · `order` · `bridge` · `contrast` · `classes` · `escape` · `generated` · `docs`. Seconds,
not minutes. `check:generated` skips with a warning when there is no Quire Ink checkout beside
this one.

```
dev/check-theme.sh
```

The gate WordPress.org actually puts a submission through — the Theme Check plugin, driven
from the command line against the running local install. Needs Docker, so it is not part of
`check:all`; run it before a release.

What it cannot tell you is that the rail is empty, that a figure is at the wrong width, or
that every headline on the listing page has picked up a link underline. All three happened
here, all three passed every check, and all three were obvious in a screenshot:

```
dev/up.sh                                          # WordPress on :8099
tools/shot.sh http://127.0.0.1:8099/<slug>/ .tmp/shots/wordpress.png
```

**Open the page.**
