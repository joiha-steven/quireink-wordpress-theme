<?php
/**
 * The one endpoint the copied reader bundle asks for.
 *
 * @package QuireInk
 */

/**
 * `/api/search?q=` — answer the header's search overlay.
 *
 * WHY THIS EXISTS. `[/find]` in the header, and the `/` key anywhere on the page, open an
 * overlay that searches as you type. That overlay is in `assets/js/core.js`, which is Quire
 * Ink's own bundle copied rather than rewritten, and it fetches `/api/search?q=…` — the blog
 * engine's route, which WordPress does not have. `if (!res.ok) return` is the bundle's whole
 * error handling, so the overlay opened, took focus, and sat saying "Type to search posts."
 * for as long as anyone typed. On every page. It was shipped that way because until the
 * origin bug was found, no render in this project had ever run JavaScript at all.
 *
 * The bundle may not be edited — `check:generated` compares it byte for byte with the engine,
 * and a second implementation of the search island is the thing that rule exists to prevent —
 * so the route is answered instead of moved.
 *
 * THE SHAPE is read off the bundle rather than guessed: it accepts a bare array or
 * `{data: […]}`, and renders each entry as `<a href="/${slug}">${title}</a>`.
 *
 * Which is why `slug` here carries the whole path under the site root and not the post's
 * slug. The bundle prefixes one slash, so on a site whose permalinks are `/%year%/%postname%/`
 * a real slug would send every result to a 404. This way the links are right under any
 * permalink structure, including plain `?p=`.
 *
 * NOT a REST route. `register_rest_route` would answer at `/wp-json/…`, and the path the
 * bundle asks for is fixed. `parse_request` is early enough to reply before WordPress decides
 * the request is a 404.
 *
 * KNOWN LIMIT: the bundle asks for `/api/search` at the DOMAIN root. On a WordPress installed
 * in a subdirectory that request never reaches WordPress, so the overlay falls back to what it
 * does with any failed fetch — nothing — and the header control is still a real link to the
 * search page, which works. Recorded in docs/gaps.md rather than papered over.
 */
function quireink_search_endpoint() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	if ( ! preg_match( '#(^|/)api/search/?$#', $path ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a public read-only
	// search, exactly like /?s= on the search page, which carries no nonce either.
	$query = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

	$results = array();
	if ( '' !== $query ) {
		$found = new WP_Query(
			array(
				's'                   => $query,
				'post_type'           => 'post',
				'post_status'         => 'publish',
				// Eight is what fits the overlay without it becoming a page of its own; the
				// header control is a real link to the full results for anyone who wants them.
				'posts_per_page'      => 8,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		foreach ( $found->posts as $result ) {
			$results[] = array(
				'slug'  => ltrim( str_replace( home_url(), '', get_permalink( $result ) ), '/' ),
				'title' => html_entity_decode( get_the_title( $result ), ENT_QUOTES, 'UTF-8' ),
			);
		}
	}

	nocache_headers();
	wp_send_json( $results );
}
add_action( 'parse_request', 'quireink_search_endpoint' );
