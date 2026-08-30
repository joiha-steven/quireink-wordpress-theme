<?php
/**
 * Quire Ink for WordPress.
 *
 * Three stylesheets, in an order that is load-bearing:
 *
 *   1. quireink-base.css    the hand-written public sheet, copied verbatim.
 *   2. quireink-tokens.css  the palette, the type scale, the shape knobs, the @font-face
 *                           block, and the generated rail geometry.
 *   3. bridge.css           the only file written for WordPress. It teaches Quire Ink's
 *                           sheet about `wp-block-*`, and nothing else belongs in it.
 *
 * THE FIRST TWO ARE IN THE BLOG ENGINE'S ORDER AND MUST STAY THERE. Quire Ink links the
 * static sheet, then the pen, then inlines the generated half LAST - and the generated half
 * is generated precisely because it has to win: `.rail` is a slide-out drawer in the static
 * sheet and only the computed media query promotes it into the desktop gutter. Enqueued the
 * intuitive way round (variables first, because everything reads them) the drawer rule wins
 * on source order, and the table of contents silently never appears on any desktop. That is
 * how this shipped for the first three screenshots.
 *
 * Anything that has to be re-derived when the blog engine moves lives in tools/extract.ts,
 * not here.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QUIREINK_VERSION', '0.1.0' );

/**
 * The reading measure, for anything that asks WordPress rather than the stylesheet.
 *
 * The same number `--shell-w` carries, and the blog engine's default. Oembeds and a few
 * plugins size themselves off this global and have no way to read a CSS variable.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 672;
}

/**
 * Theme supports.
 */
function quireink_setup() {
	load_theme_textdomain( 'quire-ink', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );

	// Not decoration: `has_custom_logo()` in header.php returns false for every site until
	// this is declared, so the wordmark an owner uploads simply never appears and the header
	// keeps showing the site name as text. It read as "the logo is a Quire Ink setting we
	// have not ported" and it was one missing line.
	add_theme_support(
		'custom-logo',
		array(
			// The blog engine's own header logo box. Flexible, because a wordmark is whatever
			// shape the wordmark is; cropping one to a square is how a signature becomes a
			// sticker.
			'height'      => 61,
			'width'       => 180,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// theme.json already declares the layout widths that make these work; the explicit calls
	// are what the block editor and Theme Check both look for.
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-spacing' );
	add_theme_support( 'appearance-tools' );
	add_theme_support( 'editor-styles' );
	// SAME ORDER AS THE FRONT END: base, then tokens, then bridge. It was tokens first here
	// while the front end ran base first, which is invariant 1 broken on the side the guard
	// could not see - `check:order` reads `wp_enqueue_style` calls and this is not one.
	//
	// quireink-ide.css is deliberately absent. Not one rule in it touches `.prose`, the post
	// title or a comment body, so in the editor it could only ever have been bytes.
	add_editor_style( array( 'assets/css/quireink-base.css', 'assets/css/quireink-tokens.css', 'assets/css/bridge.css' ) );

	register_nav_menus(
		array(
			'primary' => __( 'Rail menu', 'quire-ink' ),
		)
	);
}
add_action( 'after_setup_theme', 'quireink_setup' );

/**
 * The sheets and the two reader bundles.
 *
 * The bundles are Quire Ink's own, copied by tools/extract.ts: `core` is the chrome (palette
 * switch, rail, search overlay, back-to-top) and `post` is the article (table-of-contents
 * scrollspy, book mode, lightbox, quote copy, resume). They are ES modules and are loaded as
 * such; `wp_enqueue_script` learned the `strategy` argument in 6.3, and the theme's floor is
 * 6.5, so no filter on script_loader_tag is needed.
 */
function quireink_assets() {
	$dir = get_template_directory_uri();
	$v   = QUIREINK_VERSION;

	wp_enqueue_style( 'quireink-base', $dir . '/assets/css/quireink-base.css', array(), $v );

	/*
	 * The IDE chrome is the one part of the look an owner can switch off, so switching it off
	 * stops it being DOWNLOADED rather than merely stopping it applying: 5,652 B of gzip that
	 * a reader no longer pays for a treatment the site has decided against. Left on - which is
	 * the default - it costs 839 B of gzip over the single sheet, because the same bytes
	 * compress a little worse in two files, plus one request on an open connection.
	 *
	 * Where it lands among the sheets does not matter, and that is a property of the sheet
	 * rather than luck: every selector in it carries `html[data-ide-chrome=on]`, so it cannot
	 * tie with anything else the theme loads. It is put here because this is where it sat
	 * inside the base sheet, and a reader of this list should not have to wonder.
	 */
	if ( 'on' === get_theme_mod( 'quireink_ide_chrome', 'on' ) ) {
		wp_enqueue_style( 'quireink-ide', $dir . '/assets/css/quireink-ide.css', array( 'quireink-base' ), $v );
	}

	wp_enqueue_style( 'quireink-tokens', $dir . '/assets/css/quireink-tokens.css', array( 'quireink-base' ), $v );
	wp_enqueue_style( 'quireink-bridge', $dir . '/assets/css/bridge.css', array( 'quireink-tokens' ), $v );

	// style.css carries the theme header and no rules; WordPress still expects the handle to
	// exist, and a child theme's own style.css depends on it.
	wp_enqueue_style( 'quireink-style', get_stylesheet_uri(), array( 'quireink-bridge' ), $v );

	wp_enqueue_script( 'quireink-core', $dir . '/assets/js/core.js', array(), $v, array( 'strategy' => 'defer', 'in_footer' => false ) );

	if ( is_singular() ) {
		wp_enqueue_script( 'quireink-post', $dir . '/assets/js/post.js', array( 'quireink-core' ), $v, array( 'strategy' => 'defer', 'in_footer' => false ) );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quireink_assets' );

/**
 * Load the reader bundles as ES modules.
 *
 * They are built by Bun with `format: esm` and use `import` at the top level, so a classic
 * <script defer> parses them as a script and throws on the first import. WordPress has no
 * `type=module` argument, hence the filter.
 */
function quireink_module_type( $tag, $handle ) {
	if ( 'quireink-core' === $handle || 'quireink-post' === $handle ) {
		$tag = str_replace( ' src=', ' type="module" src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'quireink_module_type', 10, 2 );

/**
 * The attributes Quire Ink's islands read off <html>.
 *
 * `data-chrome-font` keys the mono-tracking rules; `data-motion` is the reduced-motion
 * switch; `data-ide-chrome` turns the code-block window frame on. The palette and the
 * light/dark scheme are NOT written here on purpose — core.js writes them from the reader's
 * own stored choice, and a value printed server-side would win the first paint and then be
 * overwritten, which is the flash the attribute exists to avoid.
 */
function quireink_html_attrs( $output ) {
	$attrs = array(
		'data-motion'      => get_theme_mod( 'quireink_motion', 'on' ),
		'data-chrome-font' => get_theme_mod( 'quireink_chrome_font', 'jetbrains-mono' ),
		'data-ide-chrome'  => get_theme_mod( 'quireink_ide_chrome', 'on' ),
	);
	foreach ( $attrs as $k => $val ) {
		$output .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $val ) );
	}
	return $output;
}
add_filter( 'language_attributes', 'quireink_html_attrs' );

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

require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/comment-walker.php';
require get_template_directory() . '/inc/blocks.php';
require get_template_directory() . '/inc/rail-widgets.php';
require get_template_directory() . '/inc/generated-appearance.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/appearance-css.php';
require get_template_directory() . '/inc/i18n-data.php';
