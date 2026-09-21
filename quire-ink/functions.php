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

// The same number style.css declares, and it has to stay that way: it is the `?ver=` on
// every sheet and script, so a stale value serves a reader last release's cache. It said
// 0.1.0 through the whole of 0.1.1, and `check:headers` could not see it - that guard reads
// style.css against readme.txt, and this is neither.
define( 'QUIREINK_VERSION', '0.1.4' );

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
	// quireink-look-code.css is deliberately absent. Not one rule in it touches `.prose`, the post
	// title or a comment body, so in the editor it could only ever have been bytes.
	//
	// editor.css is NOT in this list, and that is not an omission - see `quireink_editor_css()`.
	add_editor_style( array( 'assets/css/quireink-base.css', 'assets/css/quireink-tokens.css', 'assets/css/bridge.css' ) );

	/*
	 * WHAT A BRAND-NEW BLOG IS MISSING, and only that.
	 *
	 * On a site with no menu and no sticky post the rail draws Categories and Archive with a
	 * count of 1 beside each. Nothing is broken and nothing looks like the picture in the
	 * directory, because the two blocks that make this theme what it is are fed by things
	 * only an owner can supply. Starter content supplies exactly those and stops.
	 *
	 * It sets NO theme mod. A palette, a shape or a picture default chosen here would be a
	 * preview of a different theme from the one that installs, and "must not move a pixel
	 * until its owner moves one" is the rule the picture settings already keep.
	 *
	 * It runs only on a site with no content of its own, only inside the Customizer, and only
	 * until the owner presses Publish or navigates away.
	 */
	add_theme_support(
		'starter-content',
		array(
			'posts'     => array( 'about', 'contact' ),
			'nav_menus' => array(
				'primary' => array(
					'name'  => __( 'Rail menu', 'quire-ink' ),
					'items' => array( 'link_home', 'page_about', 'page_contact' ),
				),
			),
		)
	);

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
 * The attributes Quire Ink's islands read off <html>.
 *
 * `data-chrome-font` keys the mono-tracking rules; `data-motion` is the reduced-motion
 * switch; `data-look` chooses the dialect the furniture is set in. The setting behind it is
 * still called `quireink_ide_chrome`: Quire Ink renamed the treatment to a LOOK in 2.2.10,
 * but ten blogs have the old key stored and renaming it would quietly reset their choice.
 * The palette and the
 * light/dark scheme are NOT written here on purpose — core.js writes them from the reader's
 * own stored choice, and a value printed server-side would win the first paint and then be
 * overwritten, which is the flash the attribute exists to avoid.
 */
function quireink_html_attrs( $output ) {
	$attrs = array(
		'data-motion'      => get_theme_mod( 'quireink_motion', 'on' ),
		'data-chrome-font' => get_theme_mod( 'quireink_chrome_font', 'jetbrains-mono' ),
		'data-look'        => 'on' === get_theme_mod( 'quireink_ide_chrome', 'on' ) ? 'code' : 'plain',
	);
	foreach ( $attrs as $k => $val ) {
		$output .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $val ) );
	}
	return $output;
}
add_filter( 'language_attributes', 'quireink_html_attrs' );

require get_template_directory() . '/inc/assets.php';
require get_template_directory() . '/inc/content.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/post-nav.php';
require get_template_directory() . '/inc/comment-walker.php';
require get_template_directory() . '/inc/blocks.php';
require get_template_directory() . '/inc/forms.php';
require get_template_directory() . '/inc/search-api.php';
require get_template_directory() . '/inc/rail-widgets.php';
require get_template_directory() . '/inc/generated-appearance.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/appearance-css.php';
require get_template_directory() . '/inc/i18n-data.php';
