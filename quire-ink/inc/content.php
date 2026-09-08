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
 * Does the article own the gutter on this request?
 *
 * ONE answer for the two places that have to agree. `parts/rail.php` stands down on a single
 * post so the article's own table of contents can take the gutter; `quireink_toc()` has to
 * know the same thing, because it is what adds the site's menu to that gutter and must NOT
 * add it anywhere the site rail is already printed. Written out twice, the second copy would
 * survive the first being changed - and it did, for one commit: a page carries the site rail
 * AND calls `quireink_toc()`, so a menu-only gutter put two rails on it, which is the column
 * of links over a column of links this whole arrangement exists to avoid.
 *
 * @return bool
 */
function quireink_article_owns_gutter() {
	return is_singular( 'post' );
}

/**
 * The site's menu, as a rail block.
 *
 * ONE renderer for two callers. The listing's rail prints it from `parts/rail-blocks.php`
 * with no heading, because there it is the first thing in the column and needs no label; the
 * article's gutter prints it under the table of contents, where it does. Written twice it
 * would have to be corrected twice, and the last correction — every level of a nested menu
 * instead of only the first — is exactly the kind that gets made in one copy.
 *
 * @param string $heading Optional visible heading, in the rail's own `<h2>` style.
 */
function quireink_rail_menu( $heading = '' ) {
	if ( ! has_nav_menu( 'primary' ) ) {
		return;
	}
	?>
	<nav aria-label="<?php esc_attr_e( 'Menu', 'quire-ink' ); ?>">
	<?php
	if ( '' !== $heading ) {
		printf( '<h2>%s</h2>', esc_html( $heading ) );
	}
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			// EVERY level, not just the first. A menu with children used to render as its top
			// level alone: the child pages an owner had put under About were in the database,
			// on the Menus screen, and nowhere on the site. A child is indented by
			// `bridge.css`, on the rail's own padding token and on both sides of it, because
			// the rail ranges left in the drawer and right in the gutter.
			'depth'          => 0,
			'items_wrap'     => '<ul>%3$s</ul>',
			// The row's text is wrapped so the chrome can range it against the divider; a bare
			// text node has nothing to align.
			'link_before'    => '<span>',
			'link_after'     => '</span>',
		)
	);
	?>
	</nav>
	<?php
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
	// One heading is a title, not a table of contents. The blog engine applies the same floor.
	$contents = count( $items ) >= 2;

	/*
	 * THE ARTICLE'S GUTTER CARRIES THE SITE MENU TOO, and that is a bug report rather than a
	 * flourish. `parts/rail.php` stands down on a single post so the gutter can hold the
	 * article's own contents - but the contents only exist above that floor, so a post with
	 * one heading or none had no rail at all. And with no `.rail` in the document, `core.js`
	 * HIDES the header's menu button:
	 *
	 *     if (!document.querySelector(".rail")) { z.hidden = true; return; }
	 *
	 * so on such a post the site's menu was not in the gutter, not in the drawer, and had no
	 * control to open it, at any width. Measured on a stock WordPress with a menu assigned to
	 * the Rail menu location: railInDom false, siteMenuInDom false, the toggle display:none.
	 *
	 * Both blocks live in one rail because the sheet lays out for one; two would be a column
	 * of links over a column of links.
	 */
	$menu = quireink_article_owns_gutter() && has_nav_menu( 'primary' );
	if ( ! $contents && ! $menu ) {
		return;
	}

	$levels   = array_unique( wp_list_pluck( $items, 'level' ) );
	$outlined = count( $levels ) > 1;
	/*
	 * `toc` STAYS ON even when there is nothing to index, and it is not decoration. The print
	 * sheet hides the article's gutter by that name (`.toc{display:none!important}`), and the
	 * IDE chrome sizes the number gutter by it. Dropped on a menu-only rail, the menu would
	 * print at the foot of every article. A `<div>` rather than a `<nav>` because it now holds
	 * two navigations, each with its own label.
	 */
	?>
	<div class="toc rail">
	<div class="rail-inner">
	<?php if ( $contents ) : ?>
	<nav aria-label="<?php esc_attr_e( 'Table of contents', 'quire-ink' ); ?>">
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
	</nav>
	<?php endif; ?>
	<?php if ( $menu ) { quireink_rail_menu( __( 'Menu', 'quire-ink' ) ); } ?>
	</div>
	</div>
	<?php
}
