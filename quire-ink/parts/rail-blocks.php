<?php
/**
 * The rail's contents, one block per idea.
 *
 * Split from `parts/rail.php` so that part can ask "is there anything to show" by rendering
 * this into a buffer and looking at it. The alternative - five `if` statements asking the
 * same questions twice, once to decide and once to render - is the shape that drifts.
 *
 * Every block is the blog engine's markup, class for class. `.rail-tags` is a flow of links
 * with a `.term-count` each; a list block is `<ul>` of `.rail-row`, and the numbers beside
 * those rows are drawn by the IDE chrome in the base sheet, not by anything here.
 *
 * @package QuireInk
 */

// ----- menu -------------------------------------------------------------------------

if ( has_nav_menu( 'primary' ) ) {
	?>
	<nav aria-label="<?php esc_attr_e( 'Menu', 'quire-ink' ); ?>">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'depth'          => 1,
			'items_wrap'     => '<ul>%3$s</ul>',
			// The row's text is wrapped so the chrome can range it against the divider; a bare
			// text node has nothing to align.
			'link_before'    => '<span>',
			'link_after'     => '</span>',
		)
	);
	?>
	</nav>
	<?php
}

// ----- featured ---------------------------------------------------------------------
//
// Sticky posts. WordPress already has the idea of "this one stays at the top", it is set
// per post in the editor, and it is what a Featured block means. A second mechanism - a
// Customizer field listing three post ids - would be the same idea with worse ergonomics.

$featured = get_posts(
	array(
		'post__in'            => get_option( 'sticky_posts' ) ? get_option( 'sticky_posts' ) : array( 0 ),
		'posts_per_page'      => 5,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
if ( $featured ) {
	?>
	<div>
		<h2><?php esc_html_e( 'Featured', 'quire-ink' ); ?></h2>
		<ul style="--count-w:1ch">
		<?php foreach ( $featured as $post ) : ?>
			<li><a class="rail-row link-accent t-small" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><span><?php echo esc_html( get_the_title( $post ) ); ?></span></a></li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

// ----- categories ---------------------------------------------------------------------

quireink_rail_terms( 'category', __( 'Categories', 'quire-ink' ), true, '' );

// ----- archive --------------------------------------------------------------------------
//
// Years, newest first, with a count each. `get_year_link()` rather than a built path: date
// archives exist whatever the permalink structure is, and the structure here is `/%postname%/`,
// which has no date in it - so the URL is WordPress's to say, not ours to assemble.

global $wpdb;
$years = $wpdb->get_results(
	"SELECT YEAR(post_date) AS y, COUNT(*) AS n
	 FROM {$wpdb->posts}
	 WHERE post_type = 'post' AND post_status = 'publish'
	 GROUP BY y ORDER BY y DESC"
);
if ( $years ) {
	?>
	<div>
		<h2><?php esc_html_e( 'Archive', 'quire-ink' ); ?></h2>
		<div class="rail-tags">
		<?php foreach ( $years as $year ) : ?>
			<a class="link-accent t-small" href="<?php echo esc_url( get_year_link( (int) $year->y ) ); ?>"><?php echo esc_html( $year->y ); ?><span class="term-count"><?php echo esc_html( number_format_i18n( $year->n ) ); ?></span></a>
		<?php endforeach; ?>
		</div>
	</div>
	<?php
}

// ----- tags -------------------------------------------------------------------------
//
// No counts, and lowercased by the sheet: a tag is a word, and thirty words each carrying a
// number is a table rather than a cloud. The blog engine makes the same distinction.

quireink_rail_terms( 'post_tag', __( 'Tags', 'quire-ink' ), false, 'lower' );
