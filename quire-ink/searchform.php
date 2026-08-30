<?php
/**
 * The search form.
 *
 * @package QuireInk
 */

?>
<form class="subscribe" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Search posts', 'quire-ink' ); ?>"
		aria-label="<?php esc_attr_e( 'Search posts', 'quire-ink' ); ?>">
	<button type="submit"><?php esc_html_e( 'Search', 'quire-ink' ); ?></button>
</form>
