<?php
/**
 * The close of the rail layout, and the footer line.
 *
 * @package QuireInk
 */

?>
</main>
<?php get_template_part( 'parts/rail' ); ?>
</div>
<footer class="site">
	<p class="footer-text">
	<?php
	printf(
		/* translators: 1: current year, 2: site name */
		esc_html__( 'Copyright %1$s %2$s', 'quireink' ),
		esc_html( wp_date( 'Y' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);
	?>
	</p>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
