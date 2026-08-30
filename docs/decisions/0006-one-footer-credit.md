# 0006 — One footer credit, and it can be switched off

**In force.**

## Decision

The footer prints a single link — **`Quire Ink theme`** → `https://quireink.com` — beside the
copyright line, and **Appearance → Customize → Quire Ink — footer** turns it off.

## Why not "powered by"

It shipped as `powered by Quire Ink` for an hour and that is a false claim. The site is
running WordPress. Quire Ink is the theme on top of it, and the blog engine is a separate
program that is not installed. "Powered by" names the engine, which is the one thing this is
not — and the reader most likely to follow the link is the reader most likely to notice.

`Quire Ink theme` says what it is in two words, and a WordPress reader already knows what a
theme credit in a footer means. Alternatives considered and not taken: `typeset with Quire
Ink`, which is accurate and better prose but invites the same question in reverse; and
`theme by Quire Ink`, which reads as an author credit rather than a name.

## Why one, and why removable

WordPress.org allows a theme exactly one credit link in the footer. Not two, not a badge, not
an image, not `target="_blank"`. So the budget is spent on the one link that matters, and the
rule is written into the file so the next person adding "just a small icon" reads it first.

Removable is the part that keeps it a credit rather than an advert. A theme that cannot be
made quiet is a theme people replace instead of configure, and the review handbook takes the
same view.

## What it is for

The theme is a distribution channel before it is anything else. Somebody installs it, likes
the reading surface, and the only thing telling them where it came from is that line.

## What it is not

It is not analytics, a phone-home, or a version ping. It is an anchor tag.
