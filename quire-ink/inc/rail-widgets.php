<?php
/**
 * The rail as a widget area.
 *
 * This reverses an earlier decision, and the reason it was wrong is worth keeping: a widget
 * area was refused on the grounds that "a gutter which accepts anything is a third of the
 * design gone". What that missed is that WordPress's widget contract and a Quire Ink rail
 * block are THE SAME SHAPE — `<div><h2>Title</h2><ul>…</ul></div>` — and the sheet already
 * styles `.rail h2`, `.rail ul` and `.rail li` generically, without caring who wrote them.
 *
 * So a widget lands in the rail looking like a rail block already. What it does not bring is
 * the row class on its links, and `quireink_rail_widgets()` adds that on the way out — the
 * same aliasing move the alignments and the image frames use, for the same reason: one
 * definition, upstream, rather than a second copy of the look in this repository.
 *
 * The area is EMPTY by default and the theme's own five blocks are what shows. Adding one
 * widget takes over the rail completely, which is the honest reading of "I want to control
 * this" — a mixed rail where half the blocks answer to the Customizer and half to the widget
 * screen is two mental models in one column.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the area, in the rail's own markup.
 */
function quireink_register_sidebar() {
	register_sidebar(
		array(
			'name'          => __( 'Rail', 'quire-ink' ),
			'id'            => 'rail',
			'description'   => __( 'The sidebar in the gutter. Leave it empty and the theme shows its own blocks: the menu, featured posts, categories, the archive and tags. Add anything here and it takes over the whole rail.', 'quire-ink' ),
			// A widget's own wrapper IS a rail block: `.rail-inner > * + *` gives it its
			// spacing and `.rail h2` its heading, with nothing added.
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'quireink_register_sidebar' );

/**
 * Print the rail's widgets, translated into the sheet's names.
 *
 * Four transforms, each undoing a difference between what a core widget emits and what Quire
 * Ink's rail is styled for. Everything else about the widget is left exactly as it came.
 */
function quireink_rail_widgets() {
	ob_start();
	dynamic_sidebar( 'rail' );
	$html = ob_get_clean();

	// 1. A tag cloud is a FLOW of links, not a list. Core calls it `.tagcloud`; the sheet
	//    calls it `.rail-tags`, and `lower` is the rule that a tag is a word.
	$html = str_replace( 'class="tagcloud"', 'class="rail-tags lower"', $html );
	$html = str_replace( 'class="tag-link-count"', 'class="term-count"', $html );

	// 2. Core sizes each tag by how often it is used, inline, from 8pt to 22pt. That is a
	//    second type scale arriving inside a design that has one, so it goes. The count is
	//    still there for anyone who wants the information.
	$html = preg_replace( '/(<a[^>]*class="[^"]*tag-cloud-link[^"]*"[^>]*)\s+style="[^"]*"/', '$1', $html );
	$html = preg_replace( '/(<a[^>]*)\s+style="font-size:[^"]*"/', '$1', $html );

	// 3. A list row: the link becomes a `.rail-row`, its text goes in a span so the flex row
	//    has something to range, and a trailing "(12)" becomes the count column the sheet
	//    already draws right-aligned in tabular figures.
	$html = preg_replace_callback(
		'/<li([^>]*)>\s*<a\s+([^>]*)>(.*?)<\/a>((?:&nbsp;|\s)*\((\d+)\))?/s',
		function ( $m ) {
			$attrs = $m[2];
			$count = isset( $m[5] ) ? $m[5] : '';
			if ( false === strpos( $attrs, 'rail-row' ) ) {
				$attrs = preg_match( '/\bclass="/', $attrs )
					? preg_replace( '/\bclass="/', 'class="rail-row link-accent t-small ', $attrs, 1 )
					: $attrs . ' class="rail-row link-accent t-small"';
			}
			$inner = '<span>' . $m[3] . '</span>';
			if ( '' !== $count ) {
				$inner .= '<span class="rail-count">' . $count . '</span>';
			}
			return '<li' . $m[1] . '><a ' . $attrs . '>' . $inner . '</a>';
		},
		$html
	);

	// 4. Whatever is left with no row class — a tag cloud link, a plugin's own markup — at
	//    least gets the rail's colour and size rather than the document's.
	//
	//    MERGED into the existing attribute, never prepended as a second one. The first
	//    version wrote `<a class="link-accent t-small" href="..." class="tag-cloud-link">`:
	//    two class attributes on one element, of which a browser keeps the first and silently
	//    drops the rest. It rendered correctly here and would have quietly deleted whatever
	//    class the next plugin depended on.
	$html = preg_replace_callback(
		'/<a\s([^>]*)>/',
		function ( $m ) {
			$attrs = $m[1];
			if ( preg_match( '/\bclass="[^"]*\b(?:rail-row|link-accent)\b/', $attrs ) ) {
				return $m[0];
			}
			$attrs = preg_match( '/\bclass="/', $attrs )
				? preg_replace( '/\bclass="/', 'class="link-accent t-small ', $attrs, 1 )
				: $attrs . ' class="link-accent t-small"';
			return '<a ' . $attrs . '>';
		},
		$html
	);

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- widget output, escaped by the widgets themselves; this pass only rewrites class names.
}
