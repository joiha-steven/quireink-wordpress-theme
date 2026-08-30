<?php
/**
 * Put the fetched articles into WordPress, as blocks.
 *
 * Run through `wp eval-file`, which means WordPress is fully loaded: `wp_insert_post` runs
 * the same path the editor runs, so what lands in the database is what an author would have
 * left behind rather than a row written around the API.
 *
 * Re-running updates in place. The comparison loop is "change the theme, look again", and a
 * seed that appends a fourth copy of the same article every time makes the listing page
 * useless by the third run.
 */

$dir   = '/seed/json';
$files = glob( $dir . '/*.json' );
if ( ! $files ) {
	WP_CLI::error( "no json in $dir" );
}

foreach ( $files as $file ) {
	$d = json_decode( file_get_contents( $file ), true );
	if ( ! $d || empty( $d['title'] ) ) {
		WP_CLI::warning( "skipped $file" );
		continue;
	}

	$slug     = sanitize_title( basename( $file, '.json' ) );
	$existing = get_page_by_path( $slug, OBJECT, 'post' );

	$post = array(
		// Without an author the byline renders empty, and the byline renders TWICE (the meta
		// line and the desktop info column), so an unset author is two blank spots rather
		// than one. User 1 is the admin the installer made.
		'post_author'   => 1,
		'post_title'    => $d['title'],
		'post_name'     => $slug,
		'post_content'  => $d['content'],
		'post_status'   => 'publish',
		'post_type'     => 'post',
		'post_date_gmt' => $d['date'] ? gmdate( 'Y-m-d H:i:s', strtotime( $d['date'] ) ) : '',
	);
	if ( $post['post_date_gmt'] ) {
		$post['post_date'] = get_date_from_gmt( $post['post_date_gmt'] );
	}
	if ( $existing ) {
		$post['ID'] = $existing->ID;
	}

	$id = wp_insert_post( $post, true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $id->get_error_message() );
		continue;
	}

	if ( ! empty( $d['cats'] ) ) {
		wp_set_object_terms( $id, $d['cats'], 'category', false );
	}
	if ( ! empty( $d['tags'] ) ) {
		wp_set_object_terms( $id, $d['tags'], 'post_tag', false );
	}

	$note = empty( $d['unmapped'] ) ? '' : '  [no block for: ' . implode( ', ', $d['unmapped'] ) . ']';
	WP_CLI::log( sprintf( '%s  /%s%s', $existing ? 'updated' : 'created', $slug, $note ) );
}

// The rail needs somewhere to point. A menu of the categories the seed just created is
// enough to show the column and is not a claim about what a real site's menu holds.
$menu = wp_get_nav_menu_object( 'Rail' );
if ( ! $menu ) {
	$menu_id = wp_create_nav_menu( 'Rail' );
} else {
	$menu_id = $menu->term_id;
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}
}
foreach ( get_categories( array( 'hide_empty' => true ) ) as $cat ) {
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => $cat->name,
			'menu-item-object'    => 'category',
			'menu-item-object-id' => $cat->term_id,
			'menu-item-type'      => 'taxonomy',
			'menu-item-status'    => 'publish',
		)
	);
}
$locations            = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );
WP_CLI::log( 'rail menu: ' . count( (array) wp_get_nav_menu_items( $menu_id ) ) . ' items' );
