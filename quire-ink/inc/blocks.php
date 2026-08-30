<?php
/**
 * What the block editor can offer that Quire Ink's stylesheet already knows how to draw.
 *
 * Both of the things registered here exist because the base sheet carries rules for markup
 * the block editor has no way to produce. That is the only reason either is here: a block
 * style whose CSS this theme had to invent would be a design decision taken in the wrong
 * layer, and the rule for that is in docs/conventions/css.md.
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
 * The callout, as a block pattern.
 *
 * `.callout` and `.callout-label` are styled upstream - an accent rule down the left and a
 * label in heading ink - and Quire Ink writes them from Markdown. Nothing in WordPress does,
 * so without this the rules sit in the sheet unreachable by any author.
 *
 * A pattern rather than a block: it is a group and two paragraphs, and a custom block would
 * be a build step, a registration and a save function for markup a pattern inserts in one
 * click.
 */
function quireink_register_block_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern(
		'quire-ink/callout',
		array(
			'title'       => __( 'Callout', 'quire-ink' ),
			'description' => __( 'A short aside with a label, set off by a rule in the accent colour.', 'quire-ink' ),
			'categories'  => array( 'text' ),
			'keywords'    => array( 'note', 'aside', 'warning' ),
			'content'     => '<!-- wp:group {"className":"callout"} --><div class="wp-block-group callout">'
				. '<!-- wp:paragraph {"className":"callout-label"} --><p class="callout-label">'
				. esc_html__( 'Note', 'quire-ink' )
				. '</p><!-- /wp:paragraph -->'
				. '<!-- wp:paragraph --><p>'
				. esc_html__( 'Something worth saying beside the argument rather than inside it.', 'quire-ink' )
				. '</p><!-- /wp:paragraph -->'
				. '</div><!-- /wp:group -->',
		)
	);
}
add_action( 'init', 'quireink_register_block_patterns' );
