<?php
/**
 * The rail: the site's own furniture, in the left gutter.
 *
 * An article replaces it with its own table of contents (`quireink_toc()`), which is why this
 * part bows out on a single post rather than stacking two rails - the sheet lays out for one,
 * and two is a column of links over a column of links.
 *
 * The blocks and their order are the blog engine's: menu, featured, categories, archive,
 * tags. Each is skipped when it has nothing to say, so a new blog shows a short rail rather
 * than five empty headings.
 *
 * ONE Quire Ink block has no counterpart and is not rendered: Loạt bài / series. WordPress
 * has no series taxonomy, and inventing one in a theme would put content structure in the
 * layer that is supposed to be about looks.
 *
 * @package QuireInk
 */

// `quireink_article_owns_gutter()` rather than the test spelled out here: `quireink_toc()`
// has to agree with this exactly, because it is what puts the site's menu in the gutter the
// article takes over, and a second copy of a condition is a second copy to forget.
if ( quireink_article_owns_gutter() ) {
	return;
}

ob_start();
get_template_part( 'parts/rail-blocks' );
$blocks = ob_get_clean();

if ( '' === trim( $blocks ) ) {
	return;
}
?>
<aside class="rail" aria-label="<?php esc_attr_e( 'Site', 'quire-ink' ); ?>">
<div class="rail-inner">
<?php echo $blocks; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts in parts/rail-blocks.php. ?>
</div>
</aside>
