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
	$all     = quireink_palettes();
	$enabled = get_theme_mod( 'quireink_palettes', array_keys( $all ) );
	if ( ! is_array( $enabled ) ) {
		$enabled = array_keys( $all );
	}
	$out = array();
	foreach ( $enabled as $id ) {
		if ( isset( $all[ $id ] ) ) {
			$out[ $id ] = $all[ $id ];
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
