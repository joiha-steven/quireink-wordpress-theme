<?php
/**
 * What comes after an article: the one link forward, and the three sideways.
 *
 * Split out of `inc/template-tags.php` when that file reached 393 of the 400-line ceiling and
 * these two needed a filter each. The seam is a real one: everything here runs AFTER the
 * words, between the last paragraph and the conversation, and nothing else in the theme
 * queries for a post that is not the one being rendered.
 *
 * Both filters exist because these are the two places where a theme guesses at an editorial
 * decision. Quire Ink has a series and picks the next piece in it; WordPress has no series,
 * so the guesses here are "the older neighbour" and "three that share a category", and a site
 * that knows better should be able to say so without forking a template.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Read next" - the one link a finished article ends on.
 *
 * Quire Ink prefers the next post in the same series and falls back to the next older post.
 * WordPress has no series, so the fallback is the whole rule here; `get_previous_post` is
 * the older neighbour despite the name.
 */
function quireink_read_next() {
	/**
	 * The post a finished article points at.
	 *
	 * The default is the older neighbour. A site with a series - a plugin, a taxonomy, an
	 * order somebody keeps by hand - returns its own post here, or `null` to print nothing.
	 *
	 * @param WP_Post|null $next    The older neighbouring post, or null if there is none.
	 * @param int          $post_id The post being read.
	 */
	$next = apply_filters( 'quireink_read_next_post', get_previous_post(), get_the_ID() );
	if ( ! $next instanceof WP_Post ) {
		return;
	}
	?>
	<hr>
	<section class="read-next">
		<p class="read-next-label"><?php esc_html_e( 'Read next', 'quire-ink' ); ?></p>
		<p class="read-next-title reading-font"><a class="link-accent" href="<?php echo esc_url( get_permalink( $next ) ); ?>"><?php echo esc_html( get_the_title( $next ) ); ?></a></p>
	</section>
	<?php
}

/**
 * Up to three posts sharing a category, newest first.
 */
function quireink_related() {
	$cats = wp_get_post_categories( get_the_ID() );
	if ( empty( $cats ) ) {
		return;
	}

	/**
	 * The query behind "Related posts".
	 *
	 * One filter rather than one for the count and one for the taxonomy: a site that wants
	 * four posts and a site that wants them drawn from tags are changing the same query, and
	 * two filters would mean two ways to leave it inconsistent. `no_found_rows` is here
	 * because this list never paginates; removing it costs a second query per article.
	 *
	 * @param array $args    Arguments for WP_Query.
	 * @param int   $post_id The post being read.
	 */
	$args = apply_filters(
		'quireink_related_args',
		array(
			'category__in'        => $cats,
			'post__not_in'        => array( get_the_ID() ),
			'posts_per_page'      => 3,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		),
		get_the_ID()
	);

	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) {
		wp_reset_postdata();
		return;
	}
	?>
	<hr>
	<section class="related">
		<h2><?php esc_html_e( 'Related posts', 'quire-ink' ); ?></h2>
		<ul>
		<?php
		while ( $q->have_posts() ) :
			$q->the_post();
			?>
			<li>
				<a class="link-accent" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				<p class="t-small text-meta"><?php echo esc_html( get_the_date() ); ?></p>
			</li>
		<?php endwhile; ?>
		</ul>
	</section>
	<?php
	wp_reset_postdata();
}
