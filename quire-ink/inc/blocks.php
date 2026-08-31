<?php
/**
 * What the block editor can offer that Quire Ink's stylesheet already knows how to draw.
 *
 * Everything registered here exists because the base sheet carries rules for markup the block
 * editor has no way to produce. That is the only reason any of it is here: a block style
 * whose CSS this theme had to invent would be a design decision taken in the wrong layer, and
 * the rule for that is in docs/conventions/css.md.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Picture frames, as block styles.
 *
 * `figure img` upstream reads four custom properties for its mat, its padding and its line,
 * and `.img-frame` + a modifier set them. The names are aliased in PHP by
 * `quireink_align_classes()`, the same way the alignments are - so the geometry keeps one
 * definition and it stays in the blog engine, where the extractor brings it across.
 */
function quireink_register_block_styles() {
	$styles = array(
		'frame'       => __( 'Framed', 'quire-ink' ),
		'frame-thin'  => __( 'Thin frame', 'quire-ink' ),
		'frame-thick' => __( 'Thick frame', 'quire-ink' ),
		'frame-ink'   => __( 'Ink frame', 'quire-ink' ),
	);
	foreach ( $styles as $name => $label ) {
		register_block_style(
			'core/image',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
}
add_action( 'init', 'quireink_register_block_styles' );

/**
 * The category the theme's patterns sit in.
 *
 * The patterns themselves are FILES, under `quire-ink/patterns/`, which WordPress registers
 * by itself - it reads the header comment of every file in that directory and translates the
 * title and the description with the theme's own text domain. That is not a block-theme
 * feature; it works for any active theme, and it is the reason there is no list of
 * `register_block_pattern()` calls here any more.
 *
 * What a file cannot declare is the category it belongs to, so that is here. Each pattern
 * also names a core category beside this one, so an author browsing `Text` or `Media` finds
 * them without knowing the theme's name.
 */
function quireink_register_pattern_category() {
	register_block_pattern_category(
		'quire-ink',
		array(
			'label'       => __( 'Quire Ink', 'quire-ink' ),
			'description' => __( 'Shapes the blog engine draws that the block editor has no other way to ask for.', 'quire-ink' ),
		)
	);
}
add_action( 'init', 'quireink_register_pattern_category' );

/**
 * Take the inline sizes off the tag cloud block.
 *
 * `wp_tag_cloud` writes `style="font-size: 8pt"` on every link and scales it by how many
 * posts carry the tag. Measured on a page holding one: six tags came out between 10.67px and
 * 29.33px, and the small end is under this theme's smallest type, which is 15px. A tag with
 * one post is not less readable than a tag with forty.
 *
 * The engine's own answer to a run of tags is `.rail-tags`, described in its sheet as a
 * wrapped run of plain words with no chips and no boxes, and that is what is left once the
 * sizes come off: the links inherit the column they sit in. Only the `font-size` declaration
 * goes; anything else an author put in that attribute stays.
 *
 * @param string $content Block HTML.
 * @param array  $block   Block, with its name.
 * @return string
 */
function quireink_tag_cloud_sizes( $content, $block ) {
	if ( ! isset( $block['blockName'] ) || 'core/tag-cloud' !== $block['blockName'] ) {
		return $content;
	}
	return preg_replace(
		array( '/\s*font-size:\s*[^;"\']*;?/', '/ style="\s*"/' ),
		array( '', '' ),
		$content
	);
}
add_filter( 'render_block', 'quireink_tag_cloud_sizes', 10, 2 );
