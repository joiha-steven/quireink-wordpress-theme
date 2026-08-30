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
<header class="list-head">
	<h1 class="t-h2 reading-font">
	<?php
	printf(
		/* translators: %s: search query */
		esc_html__( 'Search: %s', 'quireink' ),
		'<span class="term-list">' . esc_html( get_search_query() ) . '</span>'
	);
	?>
	</h1>
	<?php get_search_form(); ?>
</header>
<div class="post-list">
<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		quireink_list_row();
	endwhile;
	quireink_pagination();
else :
	get_template_part( 'parts/none' );
endif;
?>
</div>
<?php
get_footer();
