<?php
/**
 * Title: Callout
 * Slug: quire-ink/callout
 * Categories: quire-ink, text
 * Keywords: note, aside, warning
 * Description: A short aside with a label, set off by a rule in the accent colour.
 * Viewport Width: 700
 *
 * `.callout` and `.callout-label` are drawn by the blog engine's sheet, which writes them
 * from Markdown. Nothing in WordPress does, so without this the rules sit there unreachable.
 *
 * @package QuireInk
 */

?>
<!-- wp:group {"className":"callout"} -->
<div class="wp-block-group callout">
	<!-- wp:paragraph {"className":"callout-label"} -->
	<p class="callout-label"><?php esc_html_e( 'Note', 'quire-ink' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'Something worth saying beside the argument rather than inside it.', 'quire-ink' ); ?></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
