<?php
/**
 * Title: Reference table
 * Slug: quire-ink/reference-table
 * Categories: quire-ink, text
 * Keywords: table, comparison, specification, data
 * Description: A small table with a header row and a caption, drawn the way the blog engine draws tables.
 * Viewport Width: 700
 *
 * The table styling ships with the theme and nothing else reaches it: a tinted header row,
 * a line around every cell, tabular numerals, and a squeeze-to-fit on a narrow screen. The
 * pattern exists so an author finds out that it is there.
 *
 * @package QuireInk
 */

?>
<!-- wp:table -->
<figure class="wp-block-table">
	<table>
		<thead>
			<tr><th><?php esc_html_e( 'What', 'quire-ink' ); ?></th><th><?php esc_html_e( 'Before', 'quire-ink' ); ?></th><th><?php esc_html_e( 'After', 'quire-ink' ); ?></th></tr>
		</thead>
		<tbody>
			<tr><td><?php esc_html_e( 'First thing', 'quire-ink' ); ?></td><td>—</td><td>—</td></tr>
			<tr><td><?php esc_html_e( 'Second thing', 'quire-ink' ); ?></td><td>—</td><td>—</td></tr>
		</tbody>
	</table>
	<figcaption class="wp-element-caption"><?php esc_html_e( 'What the numbers are, and where they were measured.', 'quire-ink' ); ?></figcaption>
</figure>
<!-- /wp:table -->
