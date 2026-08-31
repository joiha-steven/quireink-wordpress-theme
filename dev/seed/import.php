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
		// wp_insert_post defaults this to 'closed' when the key is absent - it does NOT read
		// the `default_comment_status` option on this path. Every seeded article came in with
		// comments closed, so the comment form never rendered and the template looked broken
		// when it was the seeder that was wrong.
		'comment_status' => 'open',
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

	$note = empty( $d['stripped'] ) ? '' : '  [stripped: ' . implode( ', ', $d['stripped'] ) . ']';
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

/*
 * One post holding every core block the theme can render without a media library.
 *
 * The comment thread above exists because comment defects only appear when there are
 * comments. This exists for the same reason and a wider one: a block that nobody has ever
 * inserted is a block nobody has ever looked at. Inserting all of them at once found an
 * outline button that came out filled, a tag cloud whose smallest link measured 10.67px
 * against a theme whose smallest type is 15px, and a search button in #32373c, a colour that
 * appears in none of the six palettes.
 *
 * It is a normal published post, so it is also in the feed and the listing. Delete it on a
 * real site; on this stack it is the page to open after touching bridge.css.
 */
$blocks_file = __DIR__ . '/every-block.html';
if ( file_exists( $blocks_file ) ) {
	$existing_blocks = get_page_by_path( 'every-core-block-on-one-page', OBJECT, 'post' );
	if ( $existing_blocks ) {
		wp_update_post(
			array(
				'ID'           => $existing_blocks->ID,
				'post_content' => file_get_contents( $blocks_file ),
			)
		);
		WP_CLI::log( 'blocks: updated /every-core-block-on-one-page' );
	} else {
		wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => 'Every core block, on one page',
				'post_name'    => 'every-core-block-on-one-page',
				'post_content' => file_get_contents( $blocks_file ),
			)
		);
		WP_CLI::log( 'blocks: created /every-core-block-on-one-page' );
	}
}

/*
 * Two more surfaces nobody opens while writing a theme, for the same reason as the sampler
 * above: ticking "password protected" or splitting a post into pages is one click in the
 * editor and there is no occasion to make it. The password form arrived wearing nothing at
 * all - a browser default box and a grey system button in the middle of a reading column -
 * and the info column beside it was reporting the word count of text the password withholds.
 */
$fixtures = array(
	array(
		'slug'     => 'a-post-behind-a-password',
		'title'    => 'A post behind a password',
		'password' => 'secret',
		'content'  => '<!-- wp:paragraph --><p>The content behind the password, which a reader reaches only after typing it. The password on this one is <code>secret</code>.</p><!-- /wp:paragraph -->',
	),
	array(
		'slug'     => 'a-post-in-three-pages',
		'title'    => 'A post in three pages',
		'password' => '',
		// The page break is the INNER `<!--nextpage-->`; the block delimiter around it is not
		// what WordPress splits on, and a post written with only the delimiter comes out as one
		// page with no links under it, which looks exactly like a theme that dropped them.
		'content'  => '<!-- wp:paragraph --><p>Page one of a post split with the page-break block.</p><!-- /wp:paragraph -->'
			. '<!-- wp:nextpage --><!--nextpage--><!-- /wp:nextpage -->'
			. '<!-- wp:paragraph --><p>Page two, reached through the links the theme prints under the article.</p><!-- /wp:paragraph -->'
			. '<!-- wp:nextpage --><!--nextpage--><!-- /wp:nextpage -->'
			. '<!-- wp:paragraph --><p>And page three, the last one.</p><!-- /wp:paragraph -->',
	),
);
foreach ( $fixtures as $f ) {
	$found = get_page_by_path( $f['slug'], OBJECT, 'post' );
	$args  = array(
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_title'    => $f['title'],
		'post_name'     => $f['slug'],
		'post_content'  => $f['content'],
		'post_password' => $f['password'],
	);
	if ( $found ) {
		$args['ID'] = $found->ID;
		wp_update_post( $args );
	} else {
		wp_insert_post( $args );
	}
}
WP_CLI::log( 'fixtures: password post and three-page post' );

/*
 * A comment thread, because the comment surface is otherwise never exercised.
 *
 * Everything under `#comments` is WordPress's markup wearing Quire Ink's class names, and
 * every defect found there so far was found by putting real comments in front of it: a
 * container opened in one field and closed in another, a consent checkbox printed twice, a
 * reply link with no top margin, an author whose own reply looked like a stranger's. None of
 * those are visible on an empty thread, and an empty thread is what a fresh seed left.
 *
 * Three shapes, because they fail differently: a long comment that wraps, a REPLY from the
 * post's own author (which is the only way `bypostauthor` ever fires), and a one-line comment
 * with a URL on the name.
 */
// Not any of the fixtures above. Each is the newest post the moment it is written, and each
// is a page for looking at rather than a piece of writing. A thread belongs under something
// somebody wrote, which here means one of the articles the seeder pulled off the live blog.
$fixture_ids = array();
foreach ( array( 'every-core-block-on-one-page', 'a-post-behind-a-password', 'a-post-in-three-pages' ) as $slug ) {
	$found = get_page_by_path( $slug, OBJECT, 'post' );
	if ( $found ) {
		$fixture_ids[] = $found->ID;
	}
}
$thread_post = get_posts(
	array(
		'numberposts' => 1,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'fields'      => 'ids',
		'exclude'     => $fixture_ids,
	)
);
if ( empty( $thread_post ) ) {
	WP_CLI::log( 'comments: no post to attach to' );
	return;
}
$thread_post = (int) $thread_post[0];

// Idempotent: seeding twice must not stack four threads on one article.
if ( get_comments( array( 'post_id' => $thread_post, 'count' => true ) ) > 0 ) {
	WP_CLI::log( 'comments: already present, left alone' );
	return;
}

$first = wp_insert_comment(
	array(
		'comment_post_ID'      => $thread_post,
		'comment_author'       => 'Lan Nguyễn',
		'comment_author_email' => 'lan@example.com',
		'comment_content'      => 'Bài này đúng thứ tôi đang tìm. Con NAS của tôi cũng chạy Docker, nhưng phần chứng chỉ thì tôi vẫn để Synology tự lo. Anh có gặp trục trặc gì với chỗ đó không?',
		'comment_approved'     => 1,
	)
);
wp_insert_comment(
	array(
		'comment_post_ID'      => $thread_post,
		'comment_parent'       => $first,
		'user_id'              => (int) get_post_field( 'post_author', $thread_post ),
		'comment_author'       => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $thread_post ) ),
		'comment_author_email' => 'admin@example.com',
		'comment_content'      => 'Có, và nó là lý do có bài sau. Tóm tắt: DSM gia hạn chứng chỉ nhưng không nạp lại vào reverse proxy, nên site chết lặng sau chín mươi ngày.',
		'comment_approved'     => 1,
	)
);
wp_insert_comment(
	array(
		'comment_post_ID'      => $thread_post,
		'comment_author'       => 'Trung',
		'comment_author_email' => 'trung@example.com',
		'comment_author_url'   => 'https://example.com',
		'comment_content'      => 'Một câu ngắn.',
		'comment_approved'     => 1,
	)
);
WP_CLI::log( 'comments: 3 on post ' . $thread_post . ' (one reply from the post author)' );
