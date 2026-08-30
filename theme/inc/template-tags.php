<?php
/**
 * The small pieces of an article that are markup rather than content.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One line of terms.
 *
 * `lower` is on tags and not on categories on purpose: Quire Ink lowercases a tag because a
 * tag is a word, and leaves a category alone because a category is a name.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $label    Visible label.
 * @param string $extra    Extra class for each link.
 */
function quireink_term_line( $taxonomy, $label, $extra = '' ) {
	$terms = get_the_terms( get_the_ID(), $taxonomy );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;
	}
	$links = array();
	foreach ( $terms as $t ) {
		$links[] = sprintf(
			'<a class="link-accent%1$s" href="%2$s">%3$s</a>',
			$extra ? ' ' . esc_attr( $extra ) : '',
			esc_url( get_term_link( $t ) ),
			esc_html( $t->name )
		);
	}
	printf(
		'<p class="info-terms">%1$s: <span class="term-list">%2$s</span></p>',
		esc_html( $label ),
		implode( ', ', $links ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built escaped above.
	);
}

/**
 * The foot of an article: the anchors the table of contents ends on, then the same terms
 * again for the readers who never see the desktop column.
 */
function quireink_post_terms() {
	$tags = get_the_terms( get_the_ID(), 'post_tag' );
	$cats = get_the_terms( get_the_ID(), 'category' );
	if ( ( empty( $tags ) || is_wp_error( $tags ) ) && ( empty( $cats ) || is_wp_error( $cats ) ) ) {
		return;
	}
	?>
	<span class="anchor" id="post-tags"></span><span class="anchor" id="post-categories"></span>
	<hr class="taxo-rule">
	<footer class="post-taxo t-small text-meta">
		<?php
		quireink_term_line( 'post_tag', __( 'Tags', 'quireink' ), 'lower' );
		quireink_term_line( 'category', __( 'Categories', 'quireink' ), '' );
		?>
	</footer>
	<?php
}

/**
 * "Read next" - the one link a finished article ends on.
 *
 * Quire Ink prefers the next post in the same series and falls back to the next older post.
 * WordPress has no series, so the fallback is the whole rule here; `get_previous_post` is
 * the older neighbour despite the name.
 */
function quireink_read_next() {
	$next = get_previous_post();
	if ( ! $next ) {
		return;
	}
	?>
	<hr>
	<section class="read-next">
		<p class="read-next-label"><?php esc_html_e( 'Read next', 'quireink' ); ?></p>
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
	$q = new WP_Query(
		array(
			'category__in'        => $cats,
			'post__not_in'        => array( get_the_ID() ),
			'posts_per_page'      => 3,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( ! $q->have_posts() ) {
		wp_reset_postdata();
		return;
	}
	?>
	<hr>
	<section class="related">
		<h2><?php esc_html_e( 'Related posts', 'quireink' ); ?></h2>
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

/**
 * One row in a list of posts.
 *
 * Class for class what the blog engine's listing emits, read off the live page rather than
 * guessed at: the date and the reading time are `.meta-part` spans in one `.t-small` line,
 * the title is an h2 in the READING font at h2 size, and the excerpt is body text rather
 * than meta - a list of headlines is reading, not furniture.
 *
 * The first version of this used `link-plain` on the title, which is not a class the sheet
 * defines, so every headline on the listing page carried a link underline. Visible in one
 * screenshot; invisible to everything else.
 */
function quireink_list_row() {
	$reading = quireink_reading( get_the_ID() );
	?>
	<article class="reveal">
		<p class="t-small text-meta"><time class="meta-part" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time> &middot; <span class="meta-part"><span class="num"><?php echo esc_html( number_format_i18n( $reading['minutes'] ) ); ?></span> <?php esc_html_e( 'min read', 'quireink' ); ?></span></p>
		<h2 class="reading-font mt-2 fs-h2 font-semibold"><a class="link-accent" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="reading-font mt-3 t-body text-text"><?php echo esc_html( get_the_excerpt() ); ?></p>
	</article>
	<?php
}

/**
 * Older / newer, in the shape the listing sheet expects.
 */
function quireink_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => __( 'Newer', 'quireink' ),
			'next_text'          => __( 'Older', 'quireink' ),
			'screen_reader_text' => __( 'Posts', 'quireink' ),
			'class'              => 'pagination t-small',
		)
	);
}

/**
 * Give rail menu links the classes the sheet styles.
 *
 * `wp_nav_menu` puts its classes on the <li>; Quire Ink's rail styles the <a>, because a row
 * that highlights has to be the thing the pointer is over.
 *
 * @param array $atts Link attributes.
 * @return array
 */
function quireink_nav_link_atts( $atts ) {
	$atts['class'] = trim( ( isset( $atts['class'] ) ? $atts['class'] : '' ) . ' rail-row link-accent t-small' );
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'quireink_nav_link_atts' );
