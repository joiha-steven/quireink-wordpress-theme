<?php
/**
 * What the theme does to a post's content before a reader sees it.
 *
 * Split out of `functions.php` when that file hit the 400-line ceiling, and the seam is a
 * real one rather than a cut at a convenient line: everything here reads or rewrites the
 * post's own HTML - counting its words, giving its headings ids, collecting them into the
 * rail's table of contents - and everything left there sets the theme up and puts files on
 * the page.
 *
 * @package QuireInk
 */

/**
 * Reading time and word count, the way the article header prints them.
 *
 * Quire Ink stores both on the post; WordPress does not, so they are counted here. 200 words
 * a minute is the figure the blog engine uses (`src/content/reading-time.ts`), kept the same
 * so the two render the same number for the same text rather than merely a similar one.
 *
 * @param int $post_id Post to measure.
 * @return array{words:int,minutes:int}
 */
function quireink_reading( $post_id ) {
	// Whitespace-split, markup stripped - the blog engine's `wordCount()` exactly, so the
	// two print the same number for the same text rather than merely a similar one.
	//
	// NOT `str_word_count`. It counts runs of ASCII letters, so every Vietnamese diacritic
	// splits a word in half: this article measured 4,220 words against the live site's 2,799
	// and the reading time came out at 21 minutes instead of 14.
	// A protected post is text the author declined to show, so its length is not the theme's
	// to report. The info column beside one was printing "[13] words · [1] min read" over a
	// password box, which reads as a bug to a reader as much as it is a small leak.
	if ( post_password_required( $post_id ) ) {
		return array( 'words' => 0, 'minutes' => 0 );
	}

	$text  = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
	$words = count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
	return array(
		'words'   => $words,
		'minutes' => max( 1, (int) round( $words / 200 ) ),
	);
}

/**
 * Give headings in the content an id, and collect them for the rail.
 *
 * Quire Ink's markdown pipeline slugs every h2/h3 as it renders, which is what both the
 * table of contents and the deep links into an article depend on. Gutenberg emits headings
 * with no id at all unless the author typed one, so the ids are added here on the way out —
 * and the same pass is what fills the rail, so the two can never disagree.
 *
 * @param string $html Post content.
 * @return string
 */
function quireink_anchor_headings( $html ) {
	if ( ! is_singular() || '' === trim( $html ) ) {
		return $html;
	}

	$GLOBALS['quireink_toc'] = array();

	return preg_replace_callback(
		'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
		function ( $m ) {
			$level = (int) $m[1];
			$attrs = $m[2];
			$inner = $m[3];
			$text  = wp_strip_all_tags( $inner );

			if ( preg_match( '/\sid=["\']([^"\']+)["\']/', $attrs, $has ) ) {
				$id = $has[1];
			} else {
				$id    = quireink_slug( $text );
				$attrs = ' id="' . esc_attr( $id ) . '"' . $attrs;
			}

			$GLOBALS['quireink_toc'][] = array(
				'id'    => $id,
				'text'  => $text,
				'level' => $level,
			);

			return sprintf( '<h%1$d%2$s>%3$s</h%1$d>', $level, $attrs, $inner );
		},
		$html
	);
}
add_filter( 'the_content', 'quireink_anchor_headings', 9 );

/**
 * The slug rule, matching Quire Ink's: lowercase, Vietnamese tone marks folded to ASCII,
 * everything else that is not a letter or a digit collapsed to a single hyphen.
 *
 * `sanitize_title` is close but not the same — it drops non-ASCII entirely when
 * `remove_accents` has no mapping, which turns a Vietnamese heading into an empty string and
 * two of them into the same empty anchor.
 *
 * @param string $text Heading text.
 * @return string
 */
function quireink_slug( $text ) {
	$slug = remove_accents( $text );
	$slug = strtolower( wp_strip_all_tags( $slug ) );
	$slug = preg_replace( '/[^a-z0-9]+/u', '-', $slug );
	$slug = trim( $slug, '-' );
	return '' === $slug ? 'section' : $slug;
}

/**
 * The rail's table of contents, printed from what the pass above collected.
 *
 * TWO-LEVEL ONLY WHEN THE ARTICLE IS. `rail-lead` and `rail-sub` are not "h2" and "h3" - they
 * turn the gutter counter from a flat `1 2 3` into an outline `1, 1.1, 2`, and the blog engine
 * only puts them on when the article actually has both levels. An article written entirely in
 * h3 - which is most of them, because the title is the h1 and the writer reaches for the next
 * heading that looks right - has a FLAT contents, and marking every row `rail-sub` numbers it
 * `0.1 0.2 0.3`: the outer counter never increments because no row ever claims a level above.
 * That shipped, and it is what an owner sees first, because the gutter is the only part of the
 * rail with numbers in it.
 */
function quireink_toc() {
	$items = isset( $GLOBALS['quireink_toc'] ) ? $GLOBALS['quireink_toc'] : array();
	if ( count( $items ) < 2 ) {
		// One heading is a title, not a table of contents. The blog engine applies the same floor.
		return;
	}

	$levels   = array_unique( wp_list_pluck( $items, 'level' ) );
	$outlined = count( $levels ) > 1;
	?>
	<nav class="toc rail" aria-label="<?php esc_attr_e( 'Table of contents', 'quire-ink' ); ?>">
	<div class="rail-inner">
	<h2><?php esc_html_e( 'Table of contents', 'quire-ink' ); ?></h2>
	<ul>
		<li><a class="rail-row link-accent t-small is-active" href="#top"><?php echo esc_html( get_the_title() ); ?></a></li>
		<?php
		foreach ( $items as $item ) {
			$mark = '';
			if ( $outlined ) {
				$mark = 3 === $item['level'] ? ' rail-sub' : ' rail-lead';
			}
			printf(
				'<li><a class="rail-row link-accent t-small%1$s" href="#%2$s">%3$s</a></li>',
				esc_attr( $mark ),
				esc_attr( $item['id'] ),
				esc_html( $item['text'] )
			);
		}

		// The way out of the article, at the foot of the index. The sheet drops its number
		// (`li:has(.toc-end)`), so it reads as a destination rather than another section.
		$foot = array();
		if ( has_tag() || has_category() ) {
			$foot[] = __( 'Tags', 'quire-ink' );
		}
		if ( comments_open() || get_comments_number() ) {
			$foot[] = __( 'Comments', 'quire-ink' );
		}
		if ( $foot ) {
			printf(
				'<li><a class="rail-row link-accent t-small toc-end" href="#%1$s">%2$s</a></li>',
				esc_attr( ( has_tag() || has_category() ) ? 'post-tags' : 'comments' ),
				esc_html( implode( ' / ', $foot ) )
			);
		}
		?>
	</ul>
	</div>
	</nav>
	<?php
}
