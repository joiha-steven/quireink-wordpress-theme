<?php
/**
 * Settings turned into CSS.
 *
 * Split out of `customizer.php` when that file hit the 400-line ceiling, and the seam is a
 * real one rather than a place to cut: everything left there answers "what can the owner
 * choose", and everything here answers "what stylesheet does that turn into". It is the same
 * seam the blog engine cut `content/settings-css.ts` on.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The owner's appearance settings, inlined after the stylesheets.
 *
 * The same position the blog engine puts them in: it links the static sheet and then inlines
 * the settings-derived half, so the later block wins without any ordering games. Everything
 * printed here comes from `inc/generated-appearance.php`, which tools/extract.ts produces by
 * running the blog engine's own emitters once per possible answer.
 *
 * EACH PART PRINTS NOTHING WHEN IT IS LEFT ALONE. The static sheet already carries the
 * default palette, the default typeface and the default shape; re-declaring the identical
 * thing is bytes on every page for no change on any of them.
 */
function quireink_appearance_css() {
	$out = '';

	// Palette first: the type scale and the shape read nothing from it, but a reader looking
	// at a half-applied sheet sees colour before anything else.
	$palette = get_theme_mod( 'quireink_palette', 'mono' );
	$scheme  = get_theme_mod( 'quireink_default_scheme', 'system' );
	$map     = quireink_palette_css();
	if ( ( 'mono' !== $palette || 'system' !== $scheme ) && isset( $map[ $palette ][ $scheme ] ) ) {
		$out .= $map[ $palette ][ $scheme ];
	}

	$font  = get_theme_mod( 'quireink_font', 'literata' );
	$fonts = quireink_font_css();
	if ( 'literata' !== $font && isset( $fonts[ $font ] ) ) {
		$out .= $fonts[ $font ];
	}

	$chrome  = get_theme_mod( 'quireink_chrome_font', 'jetbrains-mono' );
	$chromes = quireink_chrome_css();
	if ( 'jetbrains-mono' !== $chrome && isset( $chromes[ $chrome ] ) ) {
		$out .= $chromes[ $chrome ];
	}

	/*
	 * The site-wide picture frame. `none` is the default and emits nothing, which is also
	 * what the blog engine does: a frame is a decision about a site's voice, so a fresh
	 * install and an upgrade both get a picture with no mat until somebody asks for one.
	 *
	 * The ink variant is a MODIFIER, not a fifth frame, so the two controls compose into one
	 * key here exactly as they compose into one call upstream.
	 */
	$frame = get_theme_mod( 'quireink_figure_frame', 'none' );
	if ( 'none' !== $frame ) {
		$key    = $frame . ( get_theme_mod( 'quireink_figure_ink', false ) ? '-ink' : '' );
		$frames = quireink_figure_css();
		if ( isset( $frames[ $key ] ) ) {
			$out .= $frames[ $key ];
		}
	}

	$out .= quireink_shape_declarations();

	if ( '' === $out ) {
		return;
	}

	printf( '<style id="quireink-appearance">%s</style>', $out ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generated CSS from the blog engine's emitters plus a closed set of numeric constants.
}
add_action( 'wp_head', 'quireink_appearance_css', 20 );

/**
 * The shape variables, as Quire Ink emits them.
 *
 * The three tables below are copied from the blog engine's `content/settings-shape.ts` and
 * are the one place in this theme where a value is retyped rather than generated. They are
 * three lines each and they are marked as the exception in CLAUDE.md.
 *
 * `--density` is declared on the SAME element the static sheet declares `--sp` on, which is
 * what makes overriding it work at all: a custom property inside another one is substituted
 * using the computed value on that element, so a second `:root` block wins. Moving this to a
 * descendant would silently do nothing — the trap the blog engine documents at length.
 *
 * @return string CSS, or the empty string when all three are untouched.
 */
function quireink_shape_declarations() {
	$density = array(
		'compact' => '0.82',
		'normal'  => '1',
		'relaxed' => '1.22',
	);
	$radius  = array(
		'square' => '0px',
		'soft'   => '.5rem',
		'round'  => '1rem',
	);
	$weight  = array(
		'light'  => array( '400', '400' ),
		'normal' => array( '700', '600' ),
		'bold'   => array( '800', '700' ),
	);

	$d = get_theme_mod( 'quireink_density', 'normal' );
	$r = get_theme_mod( 'quireink_radius', 'soft' );
	$w = get_theme_mod( 'quireink_heading_weight', 'normal' );

	if ( 'normal' === $d && 'soft' === $r && 'normal' === $w ) {
		return '';
	}

	$d = isset( $density[ $d ] ) ? $d : 'normal';
	$r = isset( $radius[ $r ] ) ? $r : 'soft';
	$w = isset( $weight[ $w ] ) ? $w : 'normal';

	return sprintf(
		':root{--density:%s;--radius:%s;--fw-title:%s;--fw-heading:%s}',
		$density[ $d ],
		$radius[ $r ],
		$weight[ $w ][0],
		$weight[ $w ][1]
	);
}
