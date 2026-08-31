<?php
/**
 * Results.
 *
 * The overlay in core.js is the search a reader actually uses; this page is where a
 * bookmarked query, a browser's search field or a reader with no JavaScript lands, so it
 * repeats the form rather than assuming the overlay put the words there.
 *
 * @package QuireInk
 */

get_header();
?>
<header class="listing-head">
	<h1>
	<?php
	printf(
		/* translators: %s: search query */
		esc_html__( 'Search: %s', 'quire-ink' ),
		'<span class="term-list">' . esc_html( get_search_query() ) . '</span>'
	);
	?>
	</h1>
	<?php get_search_form(); ?>
</header>
<?php if ( have_posts() ) : ?>
<div class="post-list">
<?php
	while ( have_posts() ) :
		the_post();
		quireink_list_row();
	endwhile;
?>
</div>
<?php
	quireink_pagination();
else :
	get_template_part( 'parts/none' );
endif;
?>
<?php
get_footer();
