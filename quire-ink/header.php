<?php
/**
 * The document head, the site bar, and the opening of the rail layout.
 *
 * The markup here is Quire Ink's, element for element and class for class, because the
 * stylesheet this theme ships is Quire Ink's and it binds to those names. Where WordPress
 * wants a hook the hook goes in; where it wants a different shape, the shape here wins - a
 * `<div class="wrap">` is not negotiable with a sheet that lays the page out from it.
 *
 * @package QuireInk
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?><?php quireink_body_data(); ?>>
<?php wp_body_open(); ?>
<?php
/*
 * `book-text` is Quire Ink's book-typography switch and it lives on the WRAP, not on the
 * article: indented paragraphs, justified lines, hyphenation. manhhung.me runs with it on,
 * which is why the side-by-side did not line up until the theme could say it too.
 */
?>
<div class="wrap<?php echo esc_attr( 'on' === get_theme_mod( 'quireink_book_text', 'off' ) ? ' book-text' : '' ); ?>">
<header class="site">
<a class="skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'quire-ink' ); ?></a>
<div class="site-bar">
	<a class="title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
	<?php
	if ( has_custom_logo() ) {
		echo wp_kses_post(
			wp_get_attachment_image(
				get_theme_mod( 'custom_logo' ),
				'full',
				false,
				array(
					'class'         => 'logo',
					'fetchpriority' => 'high',
					'decoding'      => 'async',
				)
			)
		);
	} else {
		echo esc_html( get_bloginfo( 'name' ) );
	}
	?>
	</a>
	<div class="site-actions">
		<a class="icon-btn" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" data-search-open aria-label="<?php esc_attr_e( 'Search', 'quire-ink' ); ?>" title="<?php esc_attr_e( 'Search', 'quire-ink' ); ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><span class="btn-token">/<?php echo esc_html_x( 'find', 'search button token', 'quire-ink' ); ?></span></a>
		<button type="button" class="icon-btn" data-theme-toggle aria-label="<?php esc_attr_e( 'Appearance', 'quire-ink' ); ?>" title="<?php esc_attr_e( 'Appearance', 'quire-ink' ); ?>" aria-haspopup="true" aria-expanded="false"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg><span class="btn-token"><?php echo esc_html_x( 'dark', 'appearance button token', 'quire-ink' ); ?></span></button>
		<button type="button" class="icon-btn rail-toggle" data-rail-toggle aria-expanded="false" aria-label="<?php esc_attr_e( 'Menu', 'quire-ink' ); ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 8h14M8 16h11"/></svg><span class="btn-token"><?php echo esc_html_x( 'menu', 'menu button token', 'quire-ink' ); ?></span></button>
	</div>
</div>
</header>
<div class="with-rail"><main id="content">
