<?php
/**
 * Title: Further reading
 * Slug: quire-ink/further-reading
 * Categories: quire-ink, text
 * Keywords: links, sources, notes, footer
 * Description: A rule, a small heading and a list of links - what to read next, at the end of a piece.
 * Viewport Width: 700
 *
 * Written with the article's own heading and list, not with the `.related` class: that one
 * belongs to the block single.php prints below the post, and two things answering to one name
 * is how a template change starts silently restyling people's writing.
 *
 * @package QuireInk
 */

?>
<!-- wp:separator -->
<hr class="wp-block-separator has-alpha-channel-opacity"/>
<!-- /wp:separator -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Further reading', 'quire-ink' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li><?php esc_html_e( 'The thing this piece argued with', 'quire-ink' ); ?></li>
	<!-- /wp:list-item -->
	<!-- wp:list-item -->
	<li><?php esc_html_e( 'The thing to read if you want the long version', 'quire-ink' ); ?></li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->
