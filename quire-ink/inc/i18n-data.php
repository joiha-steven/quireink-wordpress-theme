<?php
/**
 * The strings Quire Ink's reader bundles look for on <body>.
 *
 * The bundles carry no text of their own - every word they put on screen is read off a
 * `data-` attribute at run time, which is how one build serves eleven languages there and
 * however many WordPress is running in here. The attribute NAMES are the bundles' contract
 * and are not ours to rename; the values are ordinary translatable strings.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every palette the extracted stylesheet carries, id => display name.
 *
 * @return array<string,string>
 */
function quireink_palettes() {
	return array(
		'mono'   => __( 'Mono', 'quire-ink' ),
		'sepia'  => __( 'Sepia', 'quire-ink' ),
		'forest' => __( 'Forest', 'quire-ink' ),
		'ocean'  => __( 'Ocean', 'quire-ink' ),
		'scifi'  => __( 'Sci-fi', 'quire-ink' ),
		'amber'  => __( 'Amber', 'quire-ink' ),
	);
}

/**
 * The palettes this site turned on, in the order they are offered.
 *
 * @return array<string,string>
 */
function quireink_enabled_palettes() {
	// One theme mod per palette, set by a checkbox each in the Customizer. This read used to
	// be a single `quireink_palettes` array that no control ever wrote — a setting with no
	// user interface, which is a default wearing a costume.
	//
	// The owner's DEFAULT palette is always in the list even if its own box is cleared: a
	// switcher that cannot return to what the site opens in is a one-way door.
	$all     = quireink_palettes();
	$default = get_theme_mod( 'quireink_palette', 'mono' );
	$out     = array();
	foreach ( $all as $id => $name ) {
		if ( $id === $default || get_theme_mod( 'quireink_palette_' . $id, true ) ) {
			$out[ $id ] = $name;
		}
	}
	return $out;
}

/**
 * Print the attributes, escaped, for the <body> tag.
 *
 * `data-palettes` is omitted below two entries: there is nothing to switch between, and the
 * switcher renders no control at all when it is absent. That is the blog engine's rule and
 * the reason this is computed rather than always printed.
 */
function quireink_body_data() {
	$palettes = quireink_enabled_palettes();

	$data = array(
		'search'         => __( 'Search', 'quire-ink' ),
		'search-hint'    => __( 'Type to search posts.', 'quire-ink' ),
		'search-empty'   => __( 'No posts matched.', 'quire-ink' ),
		'lightbox-close' => __( 'Close', 'quire-ink' ),
		'lightbox-prev'  => __( 'Previous image', 'quire-ink' ),
		'lightbox-next'  => __( 'Next image', 'quire-ink' ),
		'grid-view'      => __( 'Grid view', 'quire-ink' ),
		'list-view'      => __( 'List view', 'quire-ink' ),
		'theme'          => __( 'Appearance', 'quire-ink' ),
		'theme-light'    => __( 'Light', 'quire-ink' ),
		'theme-dark'     => __( 'Dark', 'quire-ink' ),
		'theme-system'   => __( 'Follow system', 'quire-ink' ),
		'theme-time'     => __( 'Follow the clock', 'quire-ink' ),
		'palette'        => __( 'Palette', 'quire-ink' ),
		'default-scheme' => get_theme_mod( 'quireink_default_scheme', 'system' ),
		'nl-heading'     => __( 'Subscribe to the newsletter', 'quire-ink' ),
		'nl-placeholder' => __( 'you@example.com', 'quire-ink' ),
		'nl-button'      => __( 'Subscribe', 'quire-ink' ),
		'nl-success'     => __( 'Almost there - check your inbox to confirm.', 'quire-ink' ),
		'nl-no-mail'     => __( 'Subscribed. Email is not configured, so no confirmation was sent.', 'quire-ink' ),
		'nl-invalid'     => __( 'That email address is not valid.', 'quire-ink' ),
		'nl-error'       => __( 'Something went wrong. Please try again.', 'quire-ink' ),

		/*
		 * THE ONES THAT ARE SOMEBODY'S ONLY NAME.
		 *
		 * `label()` in the bundles is `document.body.dataset[name] ?? ''`, so a key nobody
		 * supplies is not a missing translation - it is an empty string, and where that
		 * string is an `aria-label` on a button whose whole content is an SVG, it is a
		 * control a screen reader announces as "button". Back-to-top and the book-mode
		 * button both shipped that way and both looked perfectly fine on screen.
		 *
		 * Wording follows the blog engine's own `locales/en.ts`, so the two surfaces say the
		 * same words. They are ordinary translatable strings here; only the KEYS are the
		 * bundles' contract.
		 */
		'back-to-top'       => __( 'Back to top', 'quire-ink' ),
		'book-mode'         => __( 'Book mode', 'quire-ink' ),
		'book-mode-close'   => __( 'Close', 'quire-ink' ),
		'book-mode-prev'    => __( 'Previous page', 'quire-ink' ),
		'book-mode-next'    => __( 'Next page', 'quire-ink' ),
		'book-mode-smaller' => __( 'Smaller text', 'quire-ink' ),
		'book-mode-larger'  => __( 'Larger text', 'quire-ink' ),
		'copy-code'         => __( 'Copy', 'quire-ink' ),
		'copied-code'       => __( 'Copied', 'quire-ink' ),
		'quote-copy'        => __( 'Copy quote', 'quire-ink' ),
		'quote-copied'      => __( 'Copied', 'quire-ink' ),
		'resume-prompt'     => __( 'Continue where you left off?', 'quire-ink' ),

		/*
		 * Not here, and not an oversight. Every `comment*` key belongs to the blog engine's
		 * comment island, which never mounts: WordPress has a thread of its own and this
		 * theme uses it (comments.php). `sw` belongs to offline reading, which is not ported.
		 * Supplying either would put strings on the page for machinery that is not running.
		 */
	);

	if ( count( $palettes ) > 1 ) {
		$data['palettes']        = wp_json_encode( $palettes );
		$data['palette-default'] = (string) array_key_first( $palettes );
	}

	$out = '';
	foreach ( $data as $key => $value ) {
		$out .= sprintf( ' data-%s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}

	echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every value escaped above.
}
