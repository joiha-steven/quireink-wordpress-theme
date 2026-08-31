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
	//
	// editor.css is NOT in this list, and that is not an omission - see `quireink_editor_css()`.
	add_editor_style( array( 'assets/css/quireink-base.css', 'assets/css/quireink-tokens.css', 'assets/css/bridge.css' ) );

	register_nav_menus(
		array(
			'primary' => __( 'Rail menu', 'quire-ink' ),
			// A flat run of links under the credit. Not a second rail: the footer is one
			// centred line of meta text and this is another, which is where a site puts the
			// three pages that are not writing - about, contact, a privacy policy.
			'footer'  => __( 'Footer menu', 'quire-ink' ),
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
 * The article's typography, inside the editor canvas.
 *
 * THE PROBLEM. Everything a reader sees inside a post is scoped to `.prose`: the reading face,
 * the measure, the heading scale, the list indents, the blockquote rule. The editor canvas is a
 * bare `.editor-styles-wrapper` and has no such class, so none of it applied - an author wrote
 * in the mono chrome face at one width and published in a book serif at another. Every static
 * check passed the whole time, because nothing about the PAGE was wrong.
 *
 * `tools/editor-css.ts` generates those same rules addressed at `body`, which inside the
 * iframed canvas is the wrapper itself.
 *
 * WHY NOT `add_editor_style()`. It was tried, it registers cleanly, `get_editor_stylesheets()`
 * lists it, the editor settings carry its text - and the canvas never receives it, while the
 * three sheets beside it in the same call all arrive. Rather than keep guessing at what the
 * editor does to a sheet on its way in, this enqueues the file. Since 6.3 the canvas is an
 * iframe and `enqueue_block_assets` runs inside it, so the file lands as a plain stylesheet
 * with nothing rewriting it.
 *
 * `is_admin()` because that hook fires on the front end too, where these rules would be a
 * second copy of the article's typography aimed at an element that is not there.
 */
function quireink_editor_css() {
	if ( ! is_admin() ) {
		return;
	}
	/*
	 * No dependency, because ordering cannot help: the editor injects its own `<style>` blocks
	 * after every enqueued link, so a link is never last. The generated sheet wins its ties on
	 * weight instead - see tools/editor-css.ts for why that is safe against the author's own
	 * choices and only aimed at WordPress's defaults.
	 */
	wp_enqueue_style(
		'quireink-editor',
		get_template_directory_uri() . '/assets/css/editor.css',
		array(),
		QUIREINK_VERSION
	);
}
add_action( 'enqueue_block_assets', 'quireink_editor_css' );

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

require get_template_directory() . '/inc/content.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/comment-walker.php';
require get_template_directory() . '/inc/blocks.php';
require get_template_directory() . '/inc/forms.php';
require get_template_directory() . '/inc/search-api.php';
require get_template_directory() . '/inc/rail-widgets.php';
require get_template_directory() . '/inc/generated-appearance.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/appearance-css.php';
require get_template_directory() . '/inc/i18n-data.php';
