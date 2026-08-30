<?php
/**
 * Title: Two pictures side by side
 * Slug: quire-ink/two-pictures
 * Categories: quire-ink, media, gallery
 * Keywords: image, columns, before after, comparison
 * Description: Two framed pictures in a row across the wide measure - a before and an after, or two of a set.
 * Viewport Width: 1100
 *
 * Both wear the `Framed` block style, which `quireink_align_classes()` aliases to the blog
 * engine's `img-frame`: a mat, a line and the padding that goes with them, defined upstream.
 *
 * @package QuireInk
 */

?>
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:image {"className":"is-style-frame"} -->
		<figure class="wp-block-image is-style-frame"><img alt=""/></figure>
		<!-- /wp:image -->
	</div>
	<!-- /wp:column -->
	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:image {"className":"is-style-frame"} -->
		<figure class="wp-block-image is-style-frame"><img alt=""/></figure>
		<!-- /wp:image -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->
