<?php
/**
 * Forms WordPress prints for the theme, wearing the theme's own names.
 *
 * @package QuireInk
 */

/**
 * The password form, which arrived wearing nothing at all.
 *
 * `get_the_password_form()` prints a bare `<label>Password: <input></label>` and a submit
 * button with no class on any of them, so on a protected post the page showed a browser
 * default text box and a grey system button in the middle of a reading column. Nobody had
 * opened a protected post: it is one checkbox in the editor and there is no reason to tick it
 * while writing a theme.
 *
 * `.subscribe` is the blog engine's own name for a short form of one field and one button -
 * the newsletter box and the search page already use it - and its rules give the field the
 * hairline and the mono face everything else here has. The sentence above it is meta, like
 * every other line the theme says in its own voice rather than the author's.
 *
 * The action, the field name and the id are WordPress's and cannot change: `wp-login.php`
 * reads `post_password` and nothing else, and `#pwbox-<id>` is what core's own markup uses,
 * so a plugin or a script looking for it still finds it.
 *
 * @param string      $form Default form HTML, discarded.
 * @param int|WP_Post $post Post the form is for.
 * @return string
 */
function quireink_password_form( $form, $post = null ) {
	$post = get_post( $post );
	$id   = 'pwbox-' . ( $post ? $post->ID : get_the_ID() );

	return '<p class="t-small text-meta">'
		. esc_html__( 'This post is behind a password.', 'quire-ink' )
		. '</p>'
		. '<form class="subscribe post-password-form" method="post" action="'
		. esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '">'
		. '<label class="screen-reader-text" for="' . esc_attr( $id ) . '">'
		. esc_html__( 'Password', 'quire-ink' ) . '</label>'
		. '<input type="password" name="post_password" id="' . esc_attr( $id ) . '"'
		. ' autocomplete="current-password" spellcheck="false"'
		. ' placeholder="' . esc_attr__( 'Password', 'quire-ink' ) . '">'
		. '<button type="submit">' . esc_html__( 'Read it', 'quire-ink' ) . '</button>'
		. '</form>';
}
add_filter( 'the_password_form', 'quireink_password_form', 10, 2 );
