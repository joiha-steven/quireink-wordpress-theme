<?php
/**
 * Not found.
 *
 * A search field and the five most recent posts, because a bare "404" is a dead end and the
 * reader who hit it was looking for something. Quire Ink's own 404 was still bare when this
 * theme was written; this is the page that blog should have.
 *
 * @package QuireInk
 */

get_header();
?>
<header class="listing-head">
	<h1><?php esc_html_e( 'That page is not here', 'quire-ink' ); ?></h1>
	<p class="t-small text-meta"><?php esc_html_e( 'The link may be old, or the address may have a typo in it.', 'quire-ink' ); ?></p>
	<?php get_search_form(); ?>
</header>
<div class="post-list">
<?php
$recent = new WP_Query(
	array(
		'posts_per_page'      => 5,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
while ( $recent->have_posts() ) :
	$recent->the_post();
	quireink_list_row();
endwhile;
wp_reset_postdata();
?>
</div>
<?php
get_footer();
