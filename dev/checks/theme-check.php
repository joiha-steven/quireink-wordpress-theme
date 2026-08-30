<?php
/**
 * Run the Theme Check plugin from the command line.
 *
 * Theme Check is the gate WordPress.org actually puts a submission through, and it only ever
 * shipped an admin screen. Its `check_main()` is a plain function, so it runs perfectly well
 * under `wp eval-file` - which turns the gate into something that can be run on every change
 * instead of remembered before a submission.
 *
 * Output is one line per finding, REQUIRED first, so the list can be worked top to bottom.
 */

if ( ! function_exists( 'check_main' ) ) {
	WP_CLI::error( 'theme-check is not active: wp plugin install theme-check --activate' );
}

$theme = isset( $args[0] ) ? $args[0] : 'quire-ink';

// The plugin reads the theme's files off disk and needs the globals its admin page sets up.
$GLOBALS['themechecks'] = isset( $GLOBALS['themechecks'] ) ? $GLOBALS['themechecks'] : array();

$files = array();
$php   = array();
$css   = array();
$other = array();

$dir = get_theme_root( $theme ) . '/' . $theme;
$it  = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
	$path = $file->getPathname();
	$ext  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	if ( 'php' === $ext ) {
		$php[ $path ] = file_get_contents( $path );
	} elseif ( 'css' === $ext ) {
		$css[ $path ] = file_get_contents( $path );
	} else {
		$other[ $path ] = ( in_array( $ext, array( 'txt', 'json', 'md' ), true ) ) ? file_get_contents( $path ) : '';
	}
}

$ok = check_main( $php, $css, $other );

$out = array();
foreach ( $GLOBALS['themechecks'] as $check ) {
	if ( ! ( $check instanceof themecheck ) ) {
		continue;
	}
	$error = $check->getError();
	if ( is_array( $error ) ) {
		$out = array_merge( $out, $error );
	}
}

$out = array_unique( $out );

$rank = function ( $line ) {
	if ( false !== stripos( $line, 'REQUIRED' ) ) {
		return 0;
	}
	if ( false !== stripos( $line, 'WARNING' ) ) {
		return 1;
	}
	if ( false !== stripos( $line, 'RECOMMENDED' ) ) {
		return 2;
	}
	return 3;
};
usort(
	$out,
	function ( $a, $b ) use ( $rank ) {
		return $rank( $a ) <=> $rank( $b );
	}
);

$counts = array( 'REQUIRED' => 0, 'WARNING' => 0, 'RECOMMENDED' => 0, 'INFO' => 0 );
foreach ( $out as $line ) {
	$plain = trim( html_entity_decode( wp_strip_all_tags( $line ) ) );
	$plain = preg_replace( '/\s+/', ' ', $plain );
	$key   = 'INFO';
	foreach ( array( 'REQUIRED', 'WARNING', 'RECOMMENDED' ) as $k ) {
		if ( false !== stripos( $plain, $k ) ) {
			$key = $k;
			break;
		}
	}
	++$counts[ $key ];
	if ( 'INFO' !== $key ) {
		WP_CLI::log( $plain );
	}
}

WP_CLI::log( '' );
WP_CLI::log( sprintf(
	'%d REQUIRED · %d WARNING · %d RECOMMENDED · %d INFO',
	$counts['REQUIRED'], $counts['WARNING'], $counts['RECOMMENDED'], $counts['INFO']
) );
WP_CLI::log( $ok ? 'theme-check: PASS' : 'theme-check: FAIL' );
