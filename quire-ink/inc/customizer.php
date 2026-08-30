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
			'title'       => __( 'Quire Ink - shape', 'quire-ink' ),
			'priority'    => 30,
			'description' => __( 'Spacing, corners and heading weight. These are what make two Quire Ink sites look different from each other.', 'quire-ink' ),
		)
	);

	$controls = array(
		'quireink_density'        => array(
			'label'   => __( 'Density', 'quire-ink' ),
			'section' => 'quireink_shape',
			'default' => 'normal',
			'choices' => array(
				'compact' => __( 'Compact', 'quire-ink' ),
				'normal'  => __( 'Normal', 'quire-ink' ),
				'relaxed' => __( 'Relaxed', 'quire-ink' ),
			),
		),
		'quireink_radius'         => array(
			'label'   => __( 'Corners', 'quire-ink' ),
			'section' => 'quireink_shape',
			'default' => 'soft',
			'choices' => array(
				'square' => __( 'Square', 'quire-ink' ),
				'soft'   => __( 'Soft', 'quire-ink' ),
				'round'  => __( 'Round', 'quire-ink' ),
			),
		),
		'quireink_heading_weight' => array(
			'label'   => __( 'Heading weight', 'quire-ink' ),
			'section' => 'quireink_shape',
			'default' => 'normal',
			'choices' => array(
				'light'  => __( 'Light', 'quire-ink' ),
				'normal' => __( 'Normal', 'quire-ink' ),
				'bold'   => __( 'Bold', 'quire-ink' ),
			),
		),
		'quireink_default_scheme' => array(
			'label'       => __( 'A first-time visitor opens in', 'quire-ink' ),
			'description' => __( 'A reader who picks for themselves always wins over this; it decides the first paint only.', 'quire-ink' ),
			'section'     => 'quireink_reading',
			'default'     => 'system',
			'choices'     => array(
				'system' => __( 'Whatever their device is set to', 'quire-ink' ),
				'light'  => __( 'Light', 'quire-ink' ),
				'dark'   => __( 'Dark', 'quire-ink' ),
			),
		),
		'quireink_motion'         => array(
			'label'       => __( 'Motion', 'quire-ink' ),
			'description' => __( 'Off removes every transition. A reader whose system asks for reduced motion gets that regardless of this setting.', 'quire-ink' ),
			'section'     => 'quireink_reading',
			'default'     => 'on',
			'choices'     => array(
				'on'  => __( 'On', 'quire-ink' ),
				'off' => __( 'Off', 'quire-ink' ),
			),
		),
		'quireink_hero'           => array(
			'label'       => __( 'Featured image on an article', 'quire-ink' ),
			'description' => __( 'Above the title, always 3:2. Off by default: switching it on would redesign every article already published.', 'quire-ink' ),
			'section'     => 'quireink_images',
			'default'     => 'none',
			'choices'     => array(
				'none'   => __( 'None', 'quire-ink' ),
				'inline' => __( 'Above the title', 'quire-ink' ),
			),
		),
		'quireink_thumb'          => array(
			'label'       => __( 'Featured image in a list', 'quire-ink' ),
			'description' => __( 'A small square beside the words, or a wide 3:2 above them. The shape is not a further choice: a list of pictures has to look like a list.', 'quire-ink' ),
			'section'     => 'quireink_images',
			'default'     => 'none',
			'choices'     => array(
				'none' => __( 'None', 'quire-ink' ),
				'side' => __( 'Small square beside', 'quire-ink' ),
				'top'  => __( 'Wide, above', 'quire-ink' ),
			),
		),
		'quireink_book_text'      => array(
			'label'       => __( 'Book typography', 'quire-ink' ),
			'description' => __( 'Indented paragraphs, justified lines and hyphenation, the way a printed page sets them. Off is the web default: ragged right, a blank line between paragraphs.', 'quire-ink' ),
			'section'     => 'quireink_reading',
			'default'     => 'off',
			'choices'     => array(
				'off' => __( 'Off', 'quire-ink' ),
				'on'  => __( 'On', 'quire-ink' ),
			),
		),
		'quireink_ide_chrome'     => array(
			'label'   => __( 'Window frame on code blocks', 'quire-ink' ),
			'section' => 'quireink_reading',
			'default' => 'on',
			'choices' => array(
				'on'  => __( 'On', 'quire-ink' ),
				'off' => __( 'Off', 'quire-ink' ),
			),
		),
	);

	$wp_customize->add_section(
		'quireink_images',
		array(
			'title'       => __( 'Quire Ink - pictures', 'quire-ink' ),
			'priority'    => 31,
			'description' => __( 'Both are off to begin with. A blog that upgrades into a theme must not move a pixel until its owner moves one.', 'quire-ink' ),
		)
	);

	$wp_customize->add_section(
		'quireink_reading',
		array(
			'title'    => __( 'Quire Ink - reading', 'quire-ink' ),
			'priority' => 31,
		)
	);

	$wp_customize->add_section(
		'quireink_footer',
		array(
			'title'    => __( 'Quire Ink - footer', 'quire-ink' ),
			'priority' => 32,
		)
	);

	$wp_customize->add_setting(
		'quireink_credit',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$wp_customize->add_control(
		'quireink_credit',
		array(
			'label'       => __( 'Credit the theme in the footer', 'quire-ink' ),
			'description' => __( 'A single "powered by Quire Ink" link beside the copyright line.', 'quire-ink' ),
			'section'     => 'quireink_footer',
			'type'        => 'checkbox',
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
