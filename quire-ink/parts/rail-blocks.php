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

// ----- the owner's own rail, if there is one ------------------------------------------
//
// One widget takes over the whole column. A rail where three blocks answer to the Customizer
// and two to the widget screen is two mental models in one gutter, and nobody can predict
// what order they come out in.

if ( is_active_sidebar( 'rail' ) ) {
	quireink_rail_widgets();
	return;
}

// ----- menu -------------------------------------------------------------------------
//
// No heading: here the menu is the first thing in the column and a label over it would be a
// word saying what the next three words already say. The article's gutter prints the same
// block WITH one, under the table of contents, where it needs to be told apart.

quireink_rail_menu();

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
		<?php
		/*
		 * `$sticky` rather than `$post`. A template loaded through get_template_part() runs
		 * inside load_template(), which globalises `$post` - so a loop variable called `$post`
		 * here is the page's current post, overwritten. It did not show while the rail was
		 * printed from the footer, after the loop had finished with it. The rail is printed
		 * before `<main>` now.
		 */
		?>
		<?php foreach ( $featured as $sticky ) : ?>
			<li><a class="rail-row link-accent t-small" href="<?php echo esc_url( get_permalink( $sticky ) ); ?>"><span><?php echo esc_html( get_the_title( $sticky ) ); ?></span></a></li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

// ----- categories ---------------------------------------------------------------------

quireink_rail_terms( 'category', __( 'Categories', 'quire-ink' ), true, '' );

// ----- archive --------------------------------------------------------------------------
//
// Years, newest first, with a count each, from `wp_get_archives()`.
//
// NOT A QUERY OF OUR OWN. This block used to run a `SELECT YEAR(post_date), COUNT(*)` over the
// posts table by hand, which a WordPress.org reviewer is right to refuse: it reaches past every
// filter core puts on a post query, it is wrong the moment a plugin changes what "published"
// means, and it repeats a statement core already keeps warm. `wp_get_archives( type: yearly )`
// asks the same question through the front door and CACHES the answer in the `post-queries`
// group against `wp_cache_get_last_changed( 'posts' )`, so on a site with an object cache the
// second page load does not touch the database at all. The hand-written version never did.
//
// The markup is ours through `get_archives_link`, not through a regex over core's output. Core
// hands the filter the url, the year, and the count inside its own `&nbsp;(N)` suffix; the row
// is rebuilt from those three, so the theme states its own shape while core states the data.
// The filter is added and removed around the one call, because it fires for every archive list
// on the page and a widget's list is not ours to restyle.
$quireink_archive_row = static function ( $html, $url, $text, $format, $before, $after ) {
	$count = preg_match( '/\((\d+)\)/', (string) $after, $found ) ? (int) $found[1] : 0;
	return sprintf(
		'<a class="link-accent t-small" href="%1$s">%2$s<span class="term-count">%3$s</span></a>',
		esc_url( $url ),
		esc_html( $text ),
		esc_html( number_format_i18n( $count ) )
	);
};
add_filter( 'get_archives_link', $quireink_archive_row, 10, 6 );
$years = trim(
	(string) wp_get_archives(
		array(
			'type'            => 'yearly',
			'format'          => 'custom',
			'show_post_count' => true,
			'echo'            => 0,
		)
	)
);
remove_filter( 'get_archives_link', $quireink_archive_row, 10 );

if ( '' !== $years ) {
	?>
	<div>
		<h2><?php esc_html_e( 'Archive', 'quire-ink' ); ?></h2>
		<div class="rail-tags"><?php echo wp_kses_post( $years ); ?></div>
	</div>
	<?php
}

// ----- tags -------------------------------------------------------------------------
//
// No counts, and lowercased by the sheet: a tag is a word, and thirty words each carrying a
// number is a table rather than a cloud. The blog engine makes the same distinction.

quireink_rail_terms( 'post_tag', __( 'Tags', 'quire-ink' ), false, 'lower' );
