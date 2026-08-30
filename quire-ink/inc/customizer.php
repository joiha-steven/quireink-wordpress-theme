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
			'label'       => __( 'Furniture reads as source code', 'quire-ink' ),
			'description' => __( 'The "//" before every small heading, the square brackets around dates and counts, the line numbers down the sidebar and the numbering in a table of contents. It is the whole treatment, not one detail: off removes every one of them. It never touches the article, the titles or the comments - those stay a book.', 'quire-ink' ),
			'section'     => 'quireink_reading',
			'default'     => 'on',
			'choices'     => array(
				'on'  => __( 'On', 'quire-ink' ),
				'off' => __( 'Off', 'quire-ink' ),
			),
		),
	);

	$wp_customize->add_section(
		'quireink_colour',
		array(
			'title'       => __( 'Quire Ink - colour', 'quire-ink' ),
			'priority'    => 28,
			'description' => __( 'Six palettes, each with a light and a dark half. A reader who picks one is remembered; these decide what everyone else sees, and which ones they are offered.', 'quire-ink' ),
		)
	);

	$wp_customize->add_setting(
		'quireink_palette',
		array(
			'default'           => 'mono',
			'sanitize_callback' => 'quireink_sanitize_choice',
		)
	);
	$wp_customize->add_control(
		'quireink_palette',
		array(
			'label'   => __( 'Palette', 'quire-ink' ),
			'section' => 'quireink_colour',
			'type'    => 'select',
			'choices' => quireink_palettes(),
		)
	);

	// One checkbox per palette rather than a multi-select, because the Customizer has no
	// multi-select and a comma-separated text field is a place to make typing mistakes.
	// Below two enabled the switcher renders no control at all, which is the blog engine's
	// rule and the reason a site can ship exactly one palette.
	foreach ( quireink_palettes() as $quireink_id => $quireink_name ) {
		$wp_customize->add_setting(
			'quireink_palette_' . $quireink_id,
			array(
				'default'           => true,
				'sanitize_callback' => 'wp_validate_boolean',
			)
		);
		$wp_customize->add_control(
			'quireink_palette_' . $quireink_id,
			array(
				/* translators: %s: palette name */
				'label'   => sprintf( __( 'Offer %s to readers', 'quire-ink' ), $quireink_name ),
				'section' => 'quireink_colour',
				'type'    => 'checkbox',
			)
		);
	}

	$wp_customize->add_section(
		'quireink_type',
		array(
			'title'       => __( 'Quire Ink - type', 'quire-ink' ),
			'priority'    => 29,
			'description' => __( 'All self-hosted. Choosing a face also loads the reading setup measured for it - a serif runs smaller and wants tighter leading than a sans.', 'quire-ink' ),
		)
	);

	$wp_customize->add_setting(
		'quireink_font',
		array(
			'default'           => 'literata',
			'sanitize_callback' => 'quireink_sanitize_choice',
		)
	);
	$wp_customize->add_control(
		'quireink_font',
		array(
			'label'       => __( 'Reading typeface', 'quire-ink' ),
			'description' => __( 'The words themselves: articles, comments, list headlines.', 'quire-ink' ),
			'section'     => 'quireink_type',
			'type'        => 'select',
			'choices'     => array(
				'literata'     => __( 'Literata - serif, for reading', 'quire-ink' ),
				'source-serif' => __( 'Source Serif 4 - serif', 'quire-ink' ),
				'inter'        => __( 'Inter - sans', 'quire-ink' ),
				'source-sans'  => __( 'Source Sans 3 - sans', 'quire-ink' ),
			),
		)
	);

	$wp_customize->add_setting(
		'quireink_chrome_font',
		array(
			'default'           => 'jetbrains-mono',
			'sanitize_callback' => 'quireink_sanitize_choice',
		)
	);
	$wp_customize->add_control(
		'quireink_chrome_font',
		array(
			'label'       => __( 'Furniture typeface', 'quire-ink' ),
			'description' => __( 'Dates, counts, the rail, the buttons. Not the words.', 'quire-ink' ),
			'section'     => 'quireink_type',
			'type'        => 'select',
			'choices'     => array(
				'jetbrains-mono' => __( 'JetBrains Mono', 'quire-ink' ),
				'plex-mono'      => __( 'IBM Plex Mono', 'quire-ink' ),
				'inter'          => __( 'Inter', 'quire-ink' ),
				'reading'        => __( 'Same as the reading face', 'quire-ink' ),
			),
		)
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
			'description' => __( 'A single "Quire Ink theme" link beside the copyright line. It names the theme, not the engine: the site is running WordPress.', 'quire-ink' ),
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
