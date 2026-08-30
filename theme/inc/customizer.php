<?php
/**
 * The knobs.
 *
 * Deliberately the SHORT list. Quire Ink measured this across three live blogs: with 84
 * colour fields and 27 typography numbers on offer, the whole visible difference between
 * them was two colour values nobody could see. What tells two blogs apart is shape - so the
 * shape knobs are here, and a colour picker per token is not.
 *
 * Every default reproduces the extracted stylesheet exactly. A site that changes nothing
 * must render identically to Quire Ink's own defaults; that is the contract the generated
 * CSS is written against, and the reason each control below emits nothing when left alone.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function quireink_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'quireink_shape',
		array(
			'title'       => __( 'Quire Ink - shape', 'quireink' ),
			'priority'    => 30,
			'description' => __( 'Spacing, corners and heading weight. These are what make two Quire Ink sites look different from each other.', 'quireink' ),
		)
	);

	$controls = array(
		'quireink_density'        => array(
			'label'   => __( 'Density', 'quireink' ),
			'section' => 'quireink_shape',
			'default' => 'normal',
			'choices' => array(
				'compact' => __( 'Compact', 'quireink' ),
				'normal'  => __( 'Normal', 'quireink' ),
				'relaxed' => __( 'Relaxed', 'quireink' ),
			),
		),
		'quireink_radius'         => array(
			'label'   => __( 'Corners', 'quireink' ),
			'section' => 'quireink_shape',
			'default' => 'soft',
			'choices' => array(
				'square' => __( 'Square', 'quireink' ),
				'soft'   => __( 'Soft', 'quireink' ),
				'round'  => __( 'Round', 'quireink' ),
			),
		),
		'quireink_heading_weight' => array(
			'label'   => __( 'Heading weight', 'quireink' ),
			'section' => 'quireink_shape',
			'default' => 'normal',
			'choices' => array(
				'light'  => __( 'Light', 'quireink' ),
				'normal' => __( 'Normal', 'quireink' ),
				'bold'   => __( 'Bold', 'quireink' ),
			),
		),
		'quireink_default_scheme' => array(
			'label'       => __( 'A first-time visitor opens in', 'quireink' ),
			'description' => __( 'A reader who picks for themselves always wins over this; it decides the first paint only.', 'quireink' ),
			'section'     => 'quireink_reading',
			'default'     => 'system',
			'choices'     => array(
				'system' => __( 'Whatever their device is set to', 'quireink' ),
				'light'  => __( 'Light', 'quireink' ),
				'dark'   => __( 'Dark', 'quireink' ),
			),
		),
		'quireink_motion'         => array(
			'label'       => __( 'Motion', 'quireink' ),
			'description' => __( 'Off removes every transition. A reader whose system asks for reduced motion gets that regardless of this setting.', 'quireink' ),
			'section'     => 'quireink_reading',
			'default'     => 'on',
			'choices'     => array(
				'on'  => __( 'On', 'quireink' ),
				'off' => __( 'Off', 'quireink' ),
			),
		),
		'quireink_book_text'      => array(
			'label'       => __( 'Book typography', 'quireink' ),
			'description' => __( 'Indented paragraphs, justified lines and hyphenation, the way a printed page sets them. Off is the web default: ragged right, a blank line between paragraphs.', 'quireink' ),
			'section'     => 'quireink_reading',
			'default'     => 'off',
			'choices'     => array(
				'off' => __( 'Off', 'quireink' ),
				'on'  => __( 'On', 'quireink' ),
			),
		),
		'quireink_ide_chrome'     => array(
			'label'   => __( 'Window frame on code blocks', 'quireink' ),
			'section' => 'quireink_reading',
			'default' => 'on',
			'choices' => array(
				'on'  => __( 'On', 'quireink' ),
				'off' => __( 'Off', 'quireink' ),
			),
		),
	);

	$wp_customize->add_section(
		'quireink_reading',
		array(
			'title'    => __( 'Quire Ink - reading', 'quireink' ),
			'priority' => 31,
		)
	);

	foreach ( $controls as $id => $c ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $c['default'],
				'sanitize_callback' => 'quireink_sanitize_choice',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $c['label'],
				'description' => isset( $c['description'] ) ? $c['description'] : '',
				'section'     => $c['section'],
				'type'        => 'select',
				'choices'     => $c['choices'],
			)
		);
	}
}
add_action( 'customize_register', 'quireink_customize_register' );

/**
 * Accept only a value the matching control actually offers.
 *
 * @param string               $value   Submitted value.
 * @param WP_Customize_Setting $setting Setting being saved.
 * @return string
 */
function quireink_sanitize_choice( $value, $setting ) {
	$control = $setting->manager->get_control( $setting->id );
	$choices = $control ? $control->choices : array();
	return array_key_exists( $value, $choices ) ? $value : $setting->default;
}

/**
 * The shape variables, as Quire Ink emits them.
 *
 * The three tables below are copied from the blog engine's `content/settings-shape.ts` and
 * are the one place in this theme where a value is retyped rather than generated. They are
 * three lines each and tools/check-shape.ts fails when they and the blog engine disagree.
 *
 * Nothing is printed when all three are left alone: the generated stylesheet already
 * declares that exact triple, and a second identical declaration is bytes on every page for
 * no change on any of them.
 */
function quireink_shape_css() {
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
		return;
	}

	$d = isset( $density[ $d ] ) ? $d : 'normal';
	$r = isset( $radius[ $r ] ) ? $r : 'soft';
	$w = isset( $weight[ $w ] ) ? $w : 'normal';

	printf(
		'<style id="quireink-shape">:root{--density:%s;--radius:%s;--fw-title:%s;--fw-heading:%s}</style>',
		esc_html( $density[ $d ] ),
		esc_html( $radius[ $r ] ),
		esc_html( $weight[ $w ][0] ),
		esc_html( $weight[ $w ][1] )
	);
}
add_action( 'wp_head', 'quireink_shape_css', 20 );
