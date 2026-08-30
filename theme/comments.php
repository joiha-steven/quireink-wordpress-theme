<?php
/**
 * Comments.
 *
 * Quire Ink renders its thread in the browser, from an island talking to its own API.
 * WordPress has a thread already, and no such API, so this is WordPress's comments wearing
 * Quire Ink's class names - which is all they needed: the base sheet carries 35 rules for
 * `.comment-list`, `.comment`, `.comment-form` and the rest, written for the island, and
 * they apply to any markup that uses the same names.
 *
 * The section keeps the id `comments` because an article's table of contents links to it.
 *
 * @package QuireInk
 */

if ( post_password_required() ) {
	return;
}
?>
<section id="comments">

<?php if ( have_comments() ) : ?>
	<h2 class="t-h3 reading-font">
	<?php
	$quireink_count = (int) get_comments_number();
	printf(
		/* translators: %s: comment count */
		esc_html( _n( '%s comment', '%s comments', $quireink_count, 'quireink' ) ),
		esc_html( number_format_i18n( $quireink_count ) )
	);
	?>
	</h2>

	<ol class="comment-list">
	<?php
	wp_list_comments(
		array(
			'walker'      => new Quireink_Comment_Walker(),
			'style'       => 'ol',
			'short_ping'  => true,
			// No avatars. Quire Ink shows a name and a time and nothing else, and an avatar
			// is a request to Gravatar on every page load - a third-party call on a site
			// whose whole pitch is that it makes none.
			'avatar_size' => 0,
		)
	);
	?>
	</ol>

	<?php
	the_comments_pagination(
		array(
			'prev_text' => __( 'Newer comments', 'quireink' ),
			'next_text' => __( 'Older comments', 'quireink' ),
			'class'     => 'pagination t-small',
		)
	);
	?>
<?php endif; ?>

<?php if ( ! comments_open() && get_comments_number() ) : ?>
	<p class="comment-status t-small"><?php esc_html_e( 'Comments are closed.', 'quireink' ); ?></p>
<?php endif; ?>

<?php
/*
 * The form, in the island's shape.
 *
 * THREE fields in the grid, not two. `.comment-fields` is `1fr 1fr` with
 * `.comment-field:last-child{grid-column:1/-1}`, which is a layout for an ODD number: name
 * and email share the first row and the third spans. Dropping the website field - which
 * looked like a reasonable opinion about spam - left email as the last child, so it spanned
 * the full width and the pair rendered as a staircase. The sheet was telling us the field
 * count and it took a screenshot to hear it.
 *
 * The body field follows the grid rather than sitting in it, and the submit button lives in
 * `.comment-actions`, which is a flex row with `margin-left:auto` on the button - so the
 * cookie checkbox goes in beside it rather than under the fields, where WordPress puts it.
 */
$quireink_req  = (bool) get_option( 'require_name_email' );
$quireink_mark = $quireink_req ? ' <span class="required">*</span>' : '';
$quireink_need = $quireink_req ? ' required' : '';
$quireink_who  = wp_get_current_commenter();

/**
 * State the field order outright.
 *
 * WordPress has put the comment textarea above the identity fields since 4.4, and Quire Ink
 * asks who you are and then what you want to say. Reordering just `comment` is not enough:
 * `cookies` opens the `.comment-actions` row that the submit button closes, so anything
 * emitted after it lands INSIDE that row. Moving only the textarea to the end put it in the
 * button row, which is a form that looks assembled by accident.
 *
 * The order is therefore stated in full rather than nudged. Fields this theme does not
 * define are appended, so a plugin adding one is not silently dropped.
 *
 * @param array $fields Comment form fields.
 * @return array
 */
function quireink_comment_field_order( $fields ) {
	$order  = array( 'author', 'email', 'url', 'comment', 'cookies' );
	$sorted = array();
	foreach ( $order as $key ) {
		if ( isset( $fields[ $key ] ) ) {
			$sorted[ $key ] = $fields[ $key ];
			unset( $fields[ $key ] );
		}
	}
	return array_merge( $sorted, $fields );
}
add_filter( 'comment_form_fields', 'quireink_comment_field_order' );

comment_form(
	array(
		'class_form'          => 'comment-form',
		'title_reply'         => __( 'Leave a comment', 'quireink' ),
		'title_reply_to'      => __( 'Reply to %s', 'quireink' ),
		'title_reply_before'  => '<h2 class="t-h3 reading-font" id="reply-title">',
		'title_reply_after'   => '</h2>',
		'cancel_reply_before' => ' <small>',
		'cancel_reply_after'  => '</small>',
		'fields'              => array(
			'author' => '<div class="comment-fields"><p class="comment-field">'
				. '<label for="author">' . esc_html__( 'Name', 'quireink' ) . $quireink_mark . '</label>'
				. '<input id="author" name="author" type="text" value="' . esc_attr( $quireink_who['comment_author'] ) . '" maxlength="245" autocomplete="name"' . $quireink_need . '></p>',
			'email'  => '<p class="comment-field">'
				. '<label for="email">' . esc_html__( 'Email', 'quireink' )
				. ' <span class="comment-note">(' . esc_html__( 'not published', 'quireink' ) . ')</span>' . $quireink_mark . '</label>'
				. '<input id="email" name="email" type="email" value="' . esc_attr( $quireink_who['comment_author_email'] ) . '" maxlength="100" autocomplete="email"' . $quireink_need . '></p>',
			'url'    => '<p class="comment-field">'
				. '<label for="url">' . esc_html__( 'Website', 'quireink' ) . '</label>'
				. '<input id="url" name="url" type="url" value="' . esc_attr( $quireink_who['comment_author_url'] ) . '" maxlength="200" autocomplete="url"></p></div>',
			// The cookie consent goes in the action row beside the button, which is what
			// `.comment-actions` is: a flex row that pushes the button to the right.
			'cookies' => '<div class="comment-actions"><label for="wp-comment-cookies-consent" class="t-small">'
				. '<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"'
				. ( empty( $quireink_who['comment_author_email'] ) ? '' : ' checked' ) . '> '
				. esc_html__( 'Remember me on this browser', 'quireink' ) . '</label>',
		),
		'comment_field'       => '<p class="comment-field comment-body-field">'
			. '<label for="comment">' . esc_html__( 'Comment', 'quireink' ) . ' <span class="required">*</span></label>'
			. '<textarea id="comment" name="comment" rows="5" maxlength="65525" required></textarea></p>',
		// %1$s is the button, %2$s the hidden fields. The div opened by the cookies field
		// above closes here, with the button inside it.
		'submit_field'        => '%1$s</div>%2$s<p class="comment-status" role="status"></p>',
		'submit_button'       => '<button type="submit" name="%1$s" id="%2$s" class="%3$s">%4$s</button>',
		'label_submit'        => __( 'Post comment', 'quireink' ),
		'comment_notes_before' => '',
		'comment_notes_after'  => '',
	)
);
?>
</section>
