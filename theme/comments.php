<?php
/**
 * Comments.
 *
 * Quire Ink renders its thread from an island against its own API; WordPress has a thread
 * already and no such API, so this is WordPress's list wearing Quire Ink's classes. The
 * section keeps the id `comments` because the article's table of contents links to it.
 *
 * @package QuireInk
 */

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments">
<?php if ( have_comments() ) : ?>
	<h2 class="t-h3 reading-font">
	<?php
	$count = get_comments_number();
	printf(
		/* translators: %s: comment count */
		esc_html( _n( '%s comment', '%s comments', $count, 'quireink' ) ),
		esc_html( number_format_i18n( $count ) )
	);
	?>
	</h2>
	<ol class="comment-list">
	<?php
	wp_list_comments(
		array(
			'style'       => 'ol',
			'short_ping'  => true,
			'avatar_size' => 40,
		)
	);
	?>
	</ol>
	<?php
	the_comments_pagination(
		array(
			'prev_text' => __( 'Newer comments', 'quireink' ),
			'next_text' => __( 'Older comments', 'quireink' ),
		)
	);
	?>
<?php endif; ?>

<?php
if ( ! comments_open() && get_comments_number() ) :
	?>
	<p class="t-small text-meta"><?php esc_html_e( 'Comments are closed.', 'quireink' ); ?></p>
	<?php
endif;

comment_form(
	array(
		'class_form'  => 'subscribe comment-form',
		'title_reply' => __( 'Leave a comment', 'quireink' ),
	)
);
?>
</section>
