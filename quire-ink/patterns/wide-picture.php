<?php
/**
 * Title: Wide picture
 * Slug: quire-ink/wide-picture
 * Categories: quire-ink, media
 * Keywords: image, figure, caption, gutter
 * Description: A picture that noses out of the reading column into the right gutter, with a caption under it.
 * Viewport Width: 1100
 *
 * "Wide" here is not a percentage. `quireink_align_classes()` turns `alignwide` into the blog
 * engine's `img-wide`, which widens the figure by exactly one rail width plus the gutter gap -
 * so a wide picture lines up with the sidebar opposite it instead of guessing.
 *
 * Not in the first two blocks of a post: the gutter is holding the post's own facts there, and
 * the sheet pulls the picture back to the column rather than print the date across a photograph.
 *
 * @package QuireInk
 */

?>
<!-- wp:image {"align":"wide","sizeSlug":"large"} -->
<figure class="wp-block-image alignwide size-large">
	<img alt=""/>
	<figcaption class="wp-element-caption"><?php esc_html_e( 'What the picture is, for a reader who cannot see it.', 'quire-ink' ); ?></figcaption>
</figure>
<!-- /wp:image -->
