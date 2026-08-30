<?php
/**
 * The right-hand rail: the site's menu, on every page that is not an article.
 *
 * An article replaces it with its own table of contents (`quireink_toc()`), which is why
 * this part bows out on a single post rather than stacking two rails - the sheet lays out
 * for one, and two is a column of links over a column of links.
 *
 * @package QuireInk
 */

if ( is_singular( 'post' ) || ! has_nav_menu( 'primary' ) ) {
	return;
}
?>
<nav class="rail" aria-label="<?php esc_attr_e( 'Site menu', 'quireink' ); ?>">
<div class="rail-inner">
<?php
wp_nav_menu(
	array(
		'theme_location' => 'primary',
		'container'      => false,
		'depth'          => 2,
		'items_wrap'     => '<ul>%3$s</ul>',
	)
);
?>
</div>
</nav>
