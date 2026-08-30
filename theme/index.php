<?php
/**
 * The list of posts, and the fallback every other template falls back to.
 *
 * @package QuireInk
 */

get_header();
?>
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
