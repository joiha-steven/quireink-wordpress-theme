<?php
/**
 * Title: Code with a note
 * Slug: quire-ink/code-with-note
 * Categories: quire-ink, text
 * Keywords: code, command, terminal, snippet
 * Description: A code panel with a quiet line under it saying what the command does or where it is run.
 * Viewport Width: 700
 *
 * A plain panel, and that is deliberate. The blog engine draws an editor window frame around
 * a code block, but the frame keys off `pre.shiki`, which only its own highlighter emits - so
 * a WordPress code block gets the panel and not the chrome, and pretending otherwise would
 * mean this theme drawing a frame the engine never drew.
 *
 * @package QuireInk
 */

?>
<!-- wp:code -->
<pre class="wp-block-code"><code>bun run check:all</code></pre>
<!-- /wp:code -->
<!-- wp:paragraph {"className":"t-small"} -->
<p class="t-small"><?php esc_html_e( 'What it does, and where you run it.', 'quire-ink' ); ?></p>
<!-- /wp:paragraph -->
