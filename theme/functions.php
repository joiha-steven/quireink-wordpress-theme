<?php
/**
 * Quire Ink for WordPress.
 *
 * Three stylesheets, in an order that is load-bearing:
 *
 *   1. quireink-base.css    the hand-written public sheet, copied verbatim.
 *   2. quireink-ink.css     the pen, on the pages that use it.
 *   3. quireink-tokens.css  the palette, the type scale, the shape knobs, the @font-face
 *                           block, and the generated rail geometry.
 *   4. bridge.css           the only file written for WordPress. It teaches Quire Ink's
 *                           sheet about `wp-block-*`, and nothing else belongs in it.
 *
 * THE FIRST THREE ARE IN THE BLOG ENGINE'S ORDER AND MUST STAY THERE. Quire Ink links the
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
 * Theme supports.
 */
function quireink_setup() {
	load_theme_textdomain( 'quireink', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( array( 'assets/css/quireink-tokens.css', 'assets/css/quireink-base.css', 'assets/css/bridge.css' ) );

	register_nav_menus(
		array(
			'primary' => __( 'Rail menu', 'quireink' ),
		)
	);
}
add_action( 'after_setup_theme', 'quireink_setup' );

/**
 * The three sheets and the two reader bundles.
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

	// The pen, only on a page that uses it. 273 KB of generated strokes is not a tax to put
	// on a page with no <mark> and no <u> in it - Quire Ink links it per page for exactly
	// this reason (ADR 0027), and the theme keeps that bargain rather than inheriting the
	// sheet into every request.
	$after_ink = array( 'quireink-base' );
	if ( quireink_needs_ink() ) {
		wp_enqueue_style( 'quireink-ink', $dir . '/assets/css/quireink-ink.css', array( 'quireink-base' ), $v );
		$after_ink = array( 'quireink-ink' );
	}

	wp_enqueue_style( 'quireink-tokens', $dir . '/assets/css/quireink-tokens.css', $after_ink, $v );
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
 * Does anything on this request carry a pen stroke?
 *
 * Reads the RAW content rather than the rendered content on purpose: this runs at
 * `wp_enqueue_scripts`, before the loop, and rendering every post in an archive to find out
 * whether one of them has a <mark> would run the whole content filter chain twice.
 *
 * @return bool
 */
function quireink_needs_ink() {
	if ( is_singular() ) {
		$raw = get_post_field( 'post_content', get_queried_object_id() );
		return (bool) preg_match( '/<(mark|u)[\s>]/i', (string) $raw );
	}
	// A listing shows excerpts, and an excerpt is stripped of markup before it is printed.
	return false;
}

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
 */
function quireink_toc() {
	$items = isset( $GLOBALS['quireink_toc'] ) ? $GLOBALS['quireink_toc'] : array();
	if ( count( $items ) < 2 ) {
		// One heading is a title, not a table of contents. Quire Ink applies the same floor.
		return;
	}
	?>
	<nav class="toc rail" aria-label="<?php esc_attr_e( 'Table of contents', 'quireink' ); ?>">
	<div class="rail-inner">
	<h2><?php esc_html_e( 'Table of contents', 'quireink' ); ?></h2>
	<ul>
		<li><a class="rail-row link-accent t-small is-active" href="#top"><?php echo esc_html( get_the_title() ); ?></a></li>
		<?php foreach ( $items as $item ) : ?>
			<li><a class="rail-row link-accent t-small<?php echo 3 === $item['level'] ? ' rail-sub' : ''; ?>" href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
	</div>
	</nav>
	<?php
}

require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/i18n-data.php';
