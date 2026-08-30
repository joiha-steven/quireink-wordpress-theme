<?php
/**
 * A tag, a category, a date - anything that is a list of posts with a name over it.
 *
 * @package QuireInk
 */

get_header();
?>
<header class="list-head">
	<h1 class="t-h2 reading-font"><?php the_archive_title(); ?></h1>
	<?php if ( get_the_archive_description() ) : ?>
		<div class="t-small text-meta"><?php the_archive_description(); ?></div>
	<?php endif; ?>
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
