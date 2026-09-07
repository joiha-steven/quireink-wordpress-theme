<?php
/**
 * The close of the rail layout, and the footer line.
 *
 * ONE credit link, and one only. WordPress.org allows a theme a single footer credit and
 * nothing more, so this is the whole budget: no second link, no badge, no image, no
 * `target="_blank"`. It can be switched off in the Customizer, which is the part that keeps
 * it a credit rather than an advert.
 *
 * THE WORDING IS "Quire Ink theme", NOT "powered by Quire Ink". The site is running
 * WordPress; Quire Ink is the theme on top of it, and the blog engine is a different program
 * that is not here. "Powered by" claims the engine, which is the one thing this is not - and
 * the reader most likely to click that link is the one most likely to notice.
 *
 * @package QuireInk
 */

?>
</main>
<?php
/*
 * The rail is opened in header.php, BEFORE `<main>`, so that a keyboard reaches the sidebar
 * where a reader sees it - in the gutter beside the first line, not after the last link on the
 * page. It closed here for as long as it was printed here; there is nothing left to close.
 */
?>
</div>
<footer class="site">
	<?php
	if ( has_nav_menu( 'footer' ) ) {
		wp_nav_menu(
			array(
				'theme_location'       => 'footer',
				'container'            => 'nav',
				'container_class'      => 'footer-nav',
				'container_aria_label' => __( 'Footer', 'quire-ink' ),
				'menu_class'           => 'footer-menu',
				// Every level. The row stays one row: a child joins the same centred run
				// straight after its parent, because `.footer-menu` and its sub-menus are the
				// same flex flow. A footer line has no room for an indent and no gutter to
				// hang one in, and a level that renders nowhere is worse than a level that
				// renders flat.
				'depth'                => 0,
				'fallback_cb'          => false,
			)
		);
	}
	?>
	<p class="footer-text">
	<?php
	printf(
		/* translators: 1: current year, 2: site name */
		esc_html__( '© %1$s %2$s', 'quire-ink' ),
		esc_html( wp_date( 'Y' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);

	if ( get_theme_mod( 'quireink_credit', true ) ) {
		printf(
			' · <a href="%1$s" rel="noopener">%2$s</a>',
			esc_url( 'https://quireink.com' ),
			/* translators: the theme credit in the footer. The site runs WordPress; this
			   names the THEME, so avoid "powered by", which claims the blog engine. */
			esc_html__( 'Quire Ink theme', 'quire-ink' )
		);
	}
	?>
	</p>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
