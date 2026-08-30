<?php
/**
 * One article.
 *
 * The order of the children of <article> is the sheet's, not a preference: the meta line and
 * the h1 sit in a <header>, the same facts repeat in an <aside class="post-info"> that only
 * desktop shows, the table of contents is a sibling rail, and the words live in
 * `#post-body.prose`.
 *
 * THE BYLINE IS PRINTED TWICE, and that is the fix rather than the bug. `.post-meta` is
 * hidden on desktop - the facts move to the right-hand column there - so a byline printed
 * only in the meta line is invisible on every desktop screen. Quire Ink shipped exactly that
 * and it was caught by opening the page, not by a test.
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
	$reading = quireink_reading( get_the_ID() );
	?>
<article>

<header>
<p class="t-small text-meta post-meta">
	<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
	&middot; <span class="num"><?php echo esc_html( number_format_i18n( $reading['words'] ) ); ?></span> <?php esc_html_e( 'words', 'quireink' ); ?>
	&middot; <span class="num"><?php echo esc_html( number_format_i18n( $reading['minutes'] ) ); ?></span> <?php esc_html_e( 'min read', 'quireink' ); ?>
	&middot; <span class="byline"><?php echo esc_html( get_the_author() ); ?></span>
	<span class="meta-book"> &middot; <button type="button" class="book-mode-toggle" data-book-open><?php esc_html_e( 'Book mode', 'quireink' ); ?></button></span>
</p>
<h1 class="reading-font mt-2 fs-h1 font-semibold"><?php the_title(); ?></h1>
</header>

<aside class="post-info t-small text-meta">
	<p><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
	<p class="byline"><?php echo esc_html( get_the_author() ); ?></p>
	<p><span class="num"><?php echo esc_html( number_format_i18n( $reading['words'] ) ); ?></span> <?php esc_html_e( 'words', 'quireink' ); ?></p>
	<p><span class="num"><?php echo esc_html( number_format_i18n( $reading['minutes'] ) ); ?></span> <?php esc_html_e( 'min read', 'quireink' ); ?></p>
	<?php
	quireink_term_line( 'post_tag', __( 'Tags', 'quireink' ), 'lower' );
	quireink_term_line( 'category', __( 'Categories', 'quireink' ), '' );
	?>
	<p class="info-action"><button type="button" class="book-mode-toggle" data-book-open><?php esc_html_e( 'Book mode', 'quireink' ); ?></button></p>
</aside>

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

<?php
	quireink_post_terms();
	quireink_read_next();
	quireink_related();
?>

</article>
	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
endwhile;

get_footer();
