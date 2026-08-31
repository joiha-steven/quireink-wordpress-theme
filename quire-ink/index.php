<?php
/**
 * The list of posts, and the fallback every other template falls back to.
 *
 * @package QuireInk
 */

get_header();
?>
<?php
/*
 * `tl-feed` turns the list into the gutter timeline: a spine down the right-hand side, a
 * sticky year tag beside the first post of each year, a month marker beside the first post
 * of each month. All of it is CSS positioned against cards that are already there - nothing
 * measures anything, and below the timeline breakpoint the markers are simply display:none.
 */
?>
<?php if ( have_posts() ) : ?>
<div class="post-list tl-feed">
<?php
	quireink_timeline_reset();
	while ( have_posts() ) :
		the_post();
		quireink_list_row();
	endwhile;
	quireink_timeline_end();
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
