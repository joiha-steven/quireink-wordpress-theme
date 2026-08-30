<?php
/**
 * Title: Pull quote
 * Slug: quire-ink/pull-quote
 * Categories: quire-ink, text
 * Keywords: quote, citation, epigraph
 * Description: A line lifted out of the argument, ruled in the accent colour, with who said it underneath.
 * Viewport Width: 700
 *
 * The rule and the citation are the one place this theme decides something the blog engine
 * never had to: Markdown had no way to write a `<cite>`, so bridge.css answers for it.
 *
 * @package QuireInk
 */

?>
<!-- wp:pullquote -->
<figure class="wp-block-pullquote">
	<blockquote>
		<p><?php esc_html_e( 'The sentence the rest of the page is arguing towards.', 'quire-ink' ); ?></p>
		<cite><?php esc_html_e( 'Who said it', 'quire-ink' ); ?></cite>
	</blockquote>
</figure>
<!-- /wp:pullquote -->
