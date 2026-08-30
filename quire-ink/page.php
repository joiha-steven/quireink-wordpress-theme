<?php
/**
 * A page: an article with no date, no reading time and no terms.
 *
 * It keeps the table of contents. A colophon or an about page is often the longest thing on
 * a blog, and the rail is the only way back up it.
 *
 * @package QuireInk
 */

get_header();

while ( have_posts() ) :
	the_post();

	/*
	 * Render the content BEFORE anything is printed.
	 *
	 * `quireink_anchor_headings()` is a `the_content` filter: it gives every h2/h3 an id and,
	 * on the same pass, collects them for the rail. So until the content has been through the
	 * filter chain there is no table of contents to print - and the rail is printed above the
	 * article. Calling the_content() in place left the rail permanently empty, which also
	 * silently disabled the desktop three-column layout, since the sheet keys that off a
	 * `.rail` sibling being there. Caught by opening the page.
	 */
	$rendered = apply_filters( 'the_content', get_the_content() );
	?>
<article <?php post_class(); ?>>
<header>
<h1 class="reading-font mt-2 fs-h1 font-semibold"><?php the_title(); ?></h1>
</header>

<?php quireink_toc(); ?>

<div id="post-body" class="prose">
<?php
	echo $rendered; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content output, filtered above.
	wp_link_pages(
		array(
			'before' => '<nav class="page-links t-small">',
			'after'  => '</nav>',
		)
	);
?>
</div>
</article>
	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
endwhile;

get_footer();
