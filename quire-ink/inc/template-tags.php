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
		quireink_term_line( 'post_tag', __( 'Tags', 'quire-ink' ), 'lower' );
		quireink_term_line( 'category', __( 'Categories', 'quire-ink' ), '' );
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
	// Opens a year group when this post starts one, and returns the month marker, which
	// belongs INSIDE the article: the sheet positions it against the card, not the page.
	$mark = quireink_timeline_step();

	// `data-thumb` is what the sheet keys the two shapes off: `side` floats a 96px square and
	// lets the words close up underneath it, `top` puts a 3:2 above them. The attribute is
	// only set when there is a picture to put there, so a row with no featured image keeps
	// exactly the layout it has today.
	$thumb = get_theme_mod( 'quireink_thumb', 'none' );
	$thumb = ( 'none' !== $thumb && has_post_thumbnail() ) ? $thumb : '';
	?>
	<article <?php post_class( 'reveal' ); ?><?php echo $thumb ? ' data-thumb="' . esc_attr( $thumb ) . '"' : ''; ?>><?php echo $mark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built escaped in quireink_timeline_step(). ?>
		<?php if ( $thumb ) : ?>
			<a class="card-thumb" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1"><?php the_post_thumbnail( 'side' === $thumb ? 'thumbnail' : 'medium_large' ); ?></a>
		<?php endif; ?>
		<p class="t-small text-meta"><time class="meta-part" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time> &middot; <span class="meta-part"><span class="num"><?php echo esc_html( number_format_i18n( $reading['minutes'] ) ); ?></span> <?php esc_html_e( 'min read', 'quire-ink' ); ?></span></p>
		<h2 class="reading-font mt-2 fs-h2 font-semibold"><a class="link-accent" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="reading-font mt-3 t-body text-text"><?php echo esc_html( get_the_excerpt() ); ?></p>
	</article>
	<?php
}

/**
 * Newer, position, older - the pager the listing sheet already draws.
 *
 * NOT `the_posts_pagination()`. That helper puts its class list through
 * `sanitize_html_class()`, which takes ONE class and strips whitespace out of anything else,
 * so `pagination t-small` reached the page welded into `paginationt-small` - a name no sheet
 * has ever carried. The pager arrived with no rule at all: no hairline over it, no top
 * margin, no small size, sitting against the last excerpt as though it were one more line of
 * the article. Nothing was red, because every class involved was spelled correctly.
 *
 * `.pager` is the blog engine's own name for this block and its rule is where the separation
 * lives - a rule across the top, 1rem under it, the two links pushed apart. The empty spans
 * are what hold the count in the middle when only one side has a link.
 *
 * Page NUMBERS are not printed, because the engine does not print them: deep page numbers
 * are URLs a crawler walks and a reader does not use, and that is a decision recorded
 * upstream rather than a shortcut taken here.
 */
function quireink_pagination() {
	global $wp_query;

	$total = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 1;
	if ( $total < 2 ) {
		return;
	}

	$page = max( 1, (int) get_query_var( 'paged' ) );
	$prev = get_previous_posts_link( __( 'Newer', 'quire-ink' ) );
	$next = get_next_posts_link( __( 'Older', 'quire-ink' ), $total );

	printf(
		'<nav class="pager" aria-label="%1$s">%2$s<span class="pager-count">%3$s</span>%4$s</nav>',
		esc_attr__( 'Posts', 'quire-ink' ),
		$prev ? wp_kses_post( $prev ) : '<span></span>',
		esc_html( number_format_i18n( $page ) . ' / ' . number_format_i18n( $total ) ),
		$next ? wp_kses_post( $next ) : '<span></span>'
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

/**
 * Give an aligned block Quire Ink's own class beside WordPress's.
 *
 * Gutenberg says `alignwide`; the stylesheet says `img-wide`. Aliasing the NAME rather than
 * copying the RULE keeps one definition of what wide means, and keeps it upstream where
 * tools/extract.ts brings it across on every run. The first version of bridge.css copied the
 * declarations instead and immediately had the gutter measurement written down twice.
 *
 * Only blocks that render a <figure> are touched. `alignwide` on a paragraph is not a thing
 * Quire Ink has an opinion about, and inventing one here is how a translation layer turns
 * into a second design.
 *
 * @param string $html  Rendered block markup.
 * @param array  $block Parsed block.
 * @return string
 */
function quireink_align_classes( $html, $block ) {
	if ( false === strpos( $html, '<figure' ) ) {
		return $html;
	}

	$alias = array(
		// The frame block styles. `img-frame` carries the mat and the line; the other three
		// are MODIFIERS that only move the padding, so each of them brings the base along.
		'is-style-frame'       => 'img-frame',
		'is-style-frame-thin'  => 'img-frame img-frame-thin',
		'is-style-frame-thick' => 'img-frame img-frame-thick',
		'is-style-frame-ink'   => 'img-frame img-frame-ink',
		'alignwide'   => 'img-wide',
		// Full bleed maps onto WIDE, not onto the viewport: a band running edge to edge
		// inside a reading column is a shape Quire Ink measured and declined, and honouring
		// it literally would put something on the page the blog engine cannot express.
		'alignfull'   => 'img-wide',
		'alignleft'   => 'img-left',
		'alignright'  => 'img-right',
		'aligncenter' => 'img-center',
	);

	foreach ( $alias as $wp => $quire ) {
		// A value can be TWO class names (`img-frame img-frame-thin`), so the "already there"
		// test looks at the first of them; matching the pair verbatim would never fire.
		$first = strtok( $quire, ' ' );
		if ( preg_match( '/\bclass="[^"]*\b' . preg_quote( $wp, '/' ) . '\b/', $html )
			&& ! preg_match( '/\bclass="[^"]*\b' . preg_quote( $first, '/' ) . '\b/', $html ) ) {
			$html = preg_replace( '/(\bclass=")/', '$1' . $quire . ' ', $html, 1 );
		}
	}

	return $html;
}
add_filter( 'render_block', 'quireink_align_classes', 10, 2 );

/**
 * A rail block of terms: a heading, then a flow of links with an optional count each.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $label    Block heading.
 * @param bool   $counts   Print a `.term-count` beside each link.
 * @param string $extra    Extra class on the `.rail-tags` container (`lower` for tags).
 */
function quireink_rail_terms( $taxonomy, $label, $counts, $extra = '' ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => 40,
		)
	);
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;
	}
	?>
	<div>
		<h2><?php echo esc_html( $label ); ?></h2>
		<div class="rail-tags<?php echo $extra ? ' ' . esc_attr( $extra ) : ''; ?>">
		<?php foreach ( $terms as $term ) : ?>
			<a class="link-accent t-small" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?><?php
			if ( $counts ) :
				?>
				<span class="term-count"><?php echo esc_html( number_format_i18n( $term->count ) ); ?></span>
				<?php
			endif;
			?></a>
		<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * The listing's gutter timeline: a year tag beside the first post of each year, a month
 * marker beside the first post of each month.
 *
 * The shape is the blog engine's and it is load-bearing. The YEAR tag is a sticky
 * zero-size anchor that has to be the first child of a `.tl-yr` wrapper around that year's
 * posts, because it pins while its own group scrolls and the next group pushes it out. The
 * MONTH marker is a child of the article itself, absolutely positioned against it, so it
 * lines up with that card without anything measuring anything.
 *
 * A month marker is NOT printed for the first month inside a year group: the year tag is
 * already standing there, and two labels on one line reads as a mistake.
 *
 * Call `quireink_timeline_reset()` before the loop. The state is module-level because the
 * loop calls this once per post and there is nowhere else to keep it.
 */
function quireink_timeline_reset() {
	$GLOBALS['quireink_tl'] = array(
		'year'  => null,
		'month' => null,
		'open'  => false,
	);
}

/**
 * Open a year group if this post starts one, and return the month marker for it.
 *
 * @return string HTML to print inside the article, before anything else.
 */
function quireink_timeline_step() {
	$state = isset( $GLOBALS['quireink_tl'] ) ? $GLOBALS['quireink_tl'] : null;
	if ( null === $state ) {
		return '';
	}

	$year  = (int) get_the_date( 'Y' );
	$month = (int) get_the_date( 'n' );
	$mark  = '';

	if ( $year !== $state['year'] ) {
		if ( $state['open'] ) {
			echo '</div>';
		}
		printf(
			'<div class="tl-yr"><div class="tl-year" aria-hidden="true"><span class="tl-year-tag"><span class="tl-dot"></span>%s</span></div>',
			esc_html( (string) $year )
		);
		$state['open']  = true;
		$state['year']  = $year;
		// The year tag speaks for this month too.
		$state['month'] = $month;
	} elseif ( $month !== $state['month'] ) {
		$mark = sprintf(
			'<span class="tl-mark t-small" aria-hidden="true"><span class="tl-dot"></span>%s</span>',
			esc_html( wp_date( 'F', (int) get_post_time( 'U', true ) ) )
		);
		$state['month'] = $month;
	}

	$GLOBALS['quireink_tl'] = $state;
	return $mark;
}

/** Close the last year group. */
function quireink_timeline_end() {
	if ( ! empty( $GLOBALS['quireink_tl']['open'] ) ) {
		echo '</div>';
		$GLOBALS['quireink_tl']['open'] = false;
	}
}
