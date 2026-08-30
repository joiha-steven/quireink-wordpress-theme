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
		esc_html( _n( '%s comment', '%s comments', $quireink_count, 'quire-ink' ) ),
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
			'prev_text' => __( 'Newer comments', 'quire-ink' ),
			'next_text' => __( 'Older comments', 'quire-ink' ),
			'class'     => 'pagination t-small',
		)
	);
	?>
<?php endif; ?>

<?php if ( ! comments_open() && get_comments_number() ) : ?>
	<p class="comment-status t-small"><?php esc_html_e( 'Comments are closed.', 'quire-ink' ); ?></p>
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
 *
 * ONE CONTAINER, ONE SLOT. `comment_form()` prints every field except `comment` only for a
 * logged-OUT reader, so a `<div>` opened in one field and closed in another is a div that
 * goes missing by halves. `.comment-actions` used to open in the `cookies` field and close in
 * `submit_field`: signed in, the opening tag was never printed and the closing one still was,
 * so it closed `#respond` instead - the button lost the row's top margin and sat on the
 * textarea, and the id fields ended up outside the form element in the parsed DOM. The row is
 * therefore built entirely inside `submit_field`, the one slot that always prints, and the
 * cookie checkbox with it, because it belongs in that row beside the button.
 *
 * `.comment-fields` keeps its two-field pairing: author opens it, url closes it, and the
 * three of them are printed or skipped together by the same `is_user_logged_in()` test.
 */
$quireink_req  = (bool) get_option( 'require_name_email' );
$quireink_mark = $quireink_req ? ' <span class="required">*</span>' : '';
$quireink_need = $quireink_req ? ' required' : '';
$quireink_who  = wp_get_current_commenter();

/*
 * The cookie consent, gated on core's own two conditions, and the action row it sits in.
 *
 * The markup is spliced into a `sprintf()` format string, so a literal percent in it would be
 * read as a conversion and eat the button. Doubling is what sprintf wants; there is none in
 * the English, and a translation is not ours to police.
 */
$quireink_cookies = '';
if ( ! is_user_logged_in()
	&& has_action( 'set_comment_cookies', 'wp_set_comment_cookies' )
	&& get_option( 'show_comments_cookies_opt_in' ) ) {
	$quireink_cookies = '<label for="wp-comment-cookies-consent" class="t-small">'
		. '<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"'
		. ( empty( $quireink_who['comment_author_email'] ) ? '' : ' checked' ) . '> '
		. esc_html__( 'Remember me on this browser', 'quire-ink' ) . '</label>';
}
$quireink_actions = '<div class="comment-actions">'
	. str_replace( '%', '%%', $quireink_cookies )
	. '%1$s</div>%2$s<p class="comment-status" role="status"></p>';

/*
 * WordPress's "Logged in as" line, wearing Quire Ink's identity row: a strip ruled off from
 * the fields, saying who is about to speak and offering the way out. Quire Ink signs out
 * through a button because its thread is an island; here it is the logout URL, which is a
 * navigation, so it is a link. `.comment-signout` styles either.
 *
 * Core's own version of this line also carries "Required fields are marked *". A logged-out
 * reader is not shown that sentence - `comment_notes_before` is empty - and every required
 * control carries its own mark, so it is left out of both rather than one.
 */
$quireink_me       = wp_get_current_user();
$quireink_identity = sprintf(
	'<p class="comment-identity">%1$s <strong>%2$s</strong> &middot; <a class="comment-signout" href="%3$s">%4$s</a></p>',
	esc_html__( 'Commenting as', 'quire-ink' ),
	esc_html( $quireink_me->display_name ),
	esc_url( wp_logout_url( get_permalink() ) ),
	esc_html__( 'Sign out', 'quire-ink' )
);

/**
 * Shape the field list: state the order outright, and drop the cookies field.
 *
 * WordPress has put the comment textarea above the identity fields since 4.4, and Quire Ink
 * asks who you are and then what you want to say.
 *
 * The order is stated in full rather than nudged, so that reading it answers the question.
 * Fields this theme does not define are appended, so a plugin adding one is not silently
 * dropped - and it lands after the textarea rather than inside the button row, which is
 * where it used to go when that row was opened by a field.
 *
 * `cookies` is removed here and NOT by leaving it out of the list, because leaving it out
 * does not work: `comment_form()` puts it back into any field list a theme passes that has
 * no `cookies` key, on purpose, so that a theme cannot drop the consent by forgetting it.
 * The control is not being dropped - the same input, under the same name, is printed in the
 * action row beside the button, on the same two conditions core checks before adding it.
 * Omitting the key printed the consent twice, once in each place.
 *
 * @param array $fields Comment form fields.
 * @return array
 */
function quireink_comment_field_order( $fields ) {
	unset( $fields['cookies'] );

	$order  = array( 'author', 'email', 'url', 'comment' );
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
		'title_reply'         => __( 'Leave a comment', 'quire-ink' ),
		/* translators: %s: name of the person being replied to */
		'title_reply_to'      => __( 'Reply to %s', 'quire-ink' ),
		'title_reply_before'  => '<h2 class="t-h3 reading-font" id="reply-title">',
		'title_reply_after'   => '</h2>',
		'cancel_reply_before' => ' <small>',
		'cancel_reply_after'  => '</small>',
		'fields'              => array(
			'author' => '<div class="comment-fields"><p class="comment-field">'
				. '<label for="author">' . esc_html__( 'Name', 'quire-ink' ) . $quireink_mark . '</label>'
				. '<input id="author" name="author" type="text" value="' . esc_attr( $quireink_who['comment_author'] ) . '" maxlength="245" autocomplete="name"' . $quireink_need . '></p>',
			'email'  => '<p class="comment-field">'
				. '<label for="email">' . esc_html__( 'Email', 'quire-ink' )
				. ' <span class="comment-note">(' . esc_html__( 'not published', 'quire-ink' ) . ')</span>' . $quireink_mark . '</label>'
				. '<input id="email" name="email" type="email" value="' . esc_attr( $quireink_who['comment_author_email'] ) . '" maxlength="100" autocomplete="email"' . $quireink_need . '></p>',
			'url'    => '<p class="comment-field">'
				. '<label for="url">' . esc_html__( 'Website', 'quire-ink' ) . '</label>'
				. '<input id="url" name="url" type="url" value="' . esc_attr( $quireink_who['comment_author_url'] ) . '" maxlength="200" autocomplete="url"></p></div>',
		),
		'logged_in_as'        => $quireink_identity,
		'comment_field'       => '<p class="comment-field comment-body-field">'
			. '<label for="comment">' . esc_html__( 'Comment', 'quire-ink' ) . ' <span class="required">*</span></label>'
			. '<textarea id="comment" name="comment" rows="5" maxlength="65525" required></textarea></p>',
		// %1$s is the button, %2$s the hidden fields; the row, the checkbox and both ends of
		// the div are built above, where nothing can print half of them.
		'submit_field'        => $quireink_actions,
		'submit_button'       => '<button type="submit" name="%1$s" id="%2$s" class="%3$s">%4$s</button>',
		'label_submit'        => __( 'Post comment', 'quire-ink' ),
		'comment_notes_before' => '',
		'comment_notes_after'  => '',
	)
);
?>
</section>
