# 0010 — The Quire Ink Pen plugin is a companion, never a dependency

**In force.**

## Context

Quire Ink reaches WordPress as two downloads: this theme, which carries the reading surface,
and the [Quire Ink Pen plugin](https://github.com/joiha-steven/quireink-wordpress-plugin), which
carries the pen and, later, the Markdown engine.

[ADR 0003](0003-skip-what-gutenberg-cannot-express.md) is why they are two things. What the
block editor cannot author is not shipped, and the pen's three gestures had no authoring side —
so the theme shipped without them and the ink was never extracted. A plugin can register a
format type; a theme should not.

The obvious shape from here is a theme that assumes the plugin. That shape is also why most
theme-and-plugin pairs are unusable alone, and it would cost the plugin its audience: every
WordPress site, rather than the ten running this theme.

## Decision

Neither product requires the other, at any layer.

This theme specifically:

- **does not ship the pen's ink.** It is the plugin's to carry, and duplicating half a
  megabyte of stroke geometry into a theme would be a second copy to keep in step.
- **does not name the plugin** in any rule the reading surface depends on.
- **may declare `add_theme_support( 'quireink-pen' )`**, which tells the plugin that this theme
  sets its own dark-page class and the plugin may skip its scheme handling. The plugin is
  correct without it.

The contract is held in a third, private repository — neither public repository may own it,
because whichever did would be the one the other had to read — and generated down into both as
[`../pair.md`](../pair.md). `check:pair` here proves this theme's copy has not been hand-edited.

## Consequences

A reader who installs only this theme gets what they get today: the reading surface, and no
marks. That is not a degraded state, it is the product as shipped since 0.1.0.

A reader who installs only the plugin gets the pen on whatever theme they already run.

Both together is the whole thing, and each readme.txt says so. **A recommendation is not a
dependency**, and the difference is the entire decision.

## Why this is invisible without a guard

Every machine either product is developed on has both installed. The failure — a rule that
only fires when the other is present — never appears here and appears immediately on a
stranger's site. That is why the rule is mechanical on both sides rather than remembered.
