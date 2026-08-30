<?php
/**
 * Title: Standfirst
 * Slug: quire-ink/standfirst
 * Categories: quire-ink, text
 * Keywords: deck, intro, subtitle, lede
 * Description: The line under the headline that says what the piece is about, set larger and quieter than the body.
 * Viewport Width: 700
 *
 * `.deck` is the blog engine's own standfirst. It is the first paragraph of the article and
 * nothing else in WordPress produces the class.
 *
 * @package QuireInk
 */

?>
<!-- wp:paragraph {"className":"deck"} -->
<p class="deck"><?php esc_html_e( 'One sentence saying what this piece is about, and why a reader who is skimming should stop.', 'quire-ink' ); ?></p>
<!-- /wp:paragraph -->
