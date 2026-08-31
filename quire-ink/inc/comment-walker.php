<?php
/**
 * The comment tree, in Quire Ink's class names.
 *
 * A walker rather than a `callback` on `wp_list_comments`, because a callback can only
 * decide what goes INSIDE each `<li>`: the list that wraps a set of replies is emitted by
 * the walker itself and comes out as `<ol class="children">`. The sheet styles
 * `.comment-replies`, which is where the left hairline and the indent live, so the wrapper
 * is exactly the part that had to change.
 *
 * The base sheet already carries 35 rules for this tree - it was written for Quire Ink's own
 * comment island, which builds the same shape in the browser. Nothing here needs new CSS;
 * it needs the same names.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders a comment thread as `.comment-list` / `.comment` / `.comment-replies`.
 */
class Quireink_Comment_Walker extends Walker_Comment {

	/**
	 * Open a nested list of replies.
	 *
	 * @param string $output Accumulated output.
	 * @param int    $depth  Depth of the comment.
	 * @param array  $args   Arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= '<ol class="comment-replies">' . "\n";
	}

	/**
	 * Close a nested list of replies.
	 *
	 * @param string $output Accumulated output.
	 * @param int    $depth  Depth of the comment.
	 * @param array  $args   Arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= "</ol>\n";
	}

	/**
	 * One comment, HTML5 flavour.
	 *
	 * BOTH `comment()` and `html5_comment()` have to be overridden, and only one of them
	 * ever runs: `Walker_Comment::start_el` dispatches on `$args['format']`, which is
	 * `html5` for any theme that declares `add_theme_support( 'html5', ['comment-list'] )`.
	 * Overriding `comment()` alone leaves the walker rendering WordPress's default markup -
	 * "Name says:" above a bracketed date - while every class name in this file sits unused.
	 * That shipped, and the screenshot is what caught it.
	 *
	 * @param WP_Comment $comment Comment.
	 * @param int        $depth   Depth.
	 * @param array      $args    Arguments.
	 */
	protected function html5_comment( $comment, $depth, $args ) {
		$this->render( $comment, $depth, $args );
	}

	/**
	 * One comment, xhtml flavour. Same output.
	 *
	 * @param WP_Comment $comment Comment.
	 * @param int        $depth   Depth.
	 * @param array      $args    Arguments.
	 */
	protected function comment( $comment, $depth, $args ) {
		$this->render( $comment, $depth, $args );
	}

	/**
	 * The markup, in Quire Ink's names.
	 *
	 * `end_el` closes the item, so this deliberately leaves `<li>` open - that is the
	 * contract Walker_Comment works to, and closing it here silently breaks every reply.
	 *
	 * @param WP_Comment $comment Comment.
	 * @param int        $depth   Depth.
	 * @param array      $args    Arguments.
	 */
	private function render( $comment, $depth, $args ) {
		?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'comment', $comment ); ?>>
			<?php
			/*
			 * The post's own author, said in a WORD rather than in a colour.
			 *
			 * WordPress puts `bypostauthor` on the item and the sheet draws nothing for it,
			 * so in a thread the writer's own reply looked exactly like a stranger's. The
			 * marker is a word in the meta line because a colour would be this theme
			 * deciding something the blog engine has not, and because a distinction carried
			 * only by colour is not a distinction for every reader.
			 *
			 * Registered comments only: a name typed into the form is not proof of identity,
			 * and `bypostauthor` is set from the account, not from the string.
			 */
			$quireink_author = $comment->user_id
				&& (int) $comment->user_id === (int) get_post_field( 'post_author', $comment->comment_post_ID );
			?>
			<p class="comment-meta t-small">
				<span class="comment-name"><?php echo esc_html( get_comment_author( $comment ) ); ?></span>
				<?php if ( $quireink_author ) : ?>
					&middot; <?php echo esc_html_x( 'author', 'marks a comment written by the post author', 'quire-ink' ); ?>
				<?php endif; ?>
				&middot; <time datetime="<?php echo esc_attr( get_comment_date( 'c', $comment ) ); ?>"><?php echo esc_html( get_comment_date( '', $comment ) ); ?></time>
				<?php if ( '1' !== $comment->comment_approved ) : ?>
					&middot; <?php esc_html_e( 'awaiting moderation', 'quire-ink' ); ?>
				<?php endif; ?>
			</p>

			<?php
			/*
			 * The id is what core's `comment-reply.js` inserts the form AFTER, and it has to
			 * be an element INSIDE the <li>.
			 *
			 * With `add_below => 'comment'` the anchor is the <li> itself, so the form landed
			 * as a sibling of it - a <div> as a direct child of a <ul>, which no list may
			 * contain, and outside `.comment`, so the sheet's own rule for a reply form
			 * (`.comment .comment-form`, written to strip the card's border because a second
			 * bordered box inside the thread boxes a box) never applied. Replying opened a
			 * full card in the middle of the conversation.
			 */
			?>
			<div class="comment-body" id="div-comment-<?php comment_ID(); ?>">
				<?php comment_text( $comment ); ?>
			</div>

			<?php
			comment_reply_link(
				array_merge(
					$args,
					array(
						'depth'     => $depth,
						'max_depth' => isset( $args['max_depth'] ) ? $args['max_depth'] : 0,
						'reply_text' => __( 'Reply', 'quire-ink' ),
						'add_below' => 'div-comment',
					),
					array( 'before' => '', 'after' => '' )
				),
				$comment
			);
			?>
		<?php
	}

	/**
	 * Close the item.
	 *
	 * @param string     $output  Accumulated output.
	 * @param WP_Comment $comment Comment.
	 * @param int        $depth   Depth.
	 * @param array      $args    Arguments.
	 */
	public function end_el( &$output, $comment, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}

/**
 * Put the sheet's class on WordPress's reply link.
 *
 * The rules are written for a `<button class="comment-reply">` that Quire Ink's island
 * creates; WordPress makes an `<a class="comment-reply-link">`. The declarations that differ
 * between the two elements (`border`, `background`, `padding`) are inert on an anchor, so
 * adding the name is the whole job.
 *
 * @param string $link Reply link markup.
 * @return string
 */
function quireink_reply_link_class( $link ) {
	// Match the attribute whichever quote WordPress used. It writes `class="comment-reply-link"`
	// with double quotes, and this looked for single ones, so the alias had never once applied:
	// the sheet's `.comment-reply` - its meta colour, its top margin and its hover - reached
	// nothing, on every thread, since the day it was written. Nothing was red, because a filter
	// that changes nothing returns the string it was given.
	return (string) preg_replace(
		'/class=(["\'])comment-reply-link/',
		'class=$1comment-reply comment-reply-link',
		$link
	);
}
add_filter( 'comment_reply_link', 'quireink_reply_link_class' );
