<?php
/**
 * What the theme puts on the page: four sheets, two bundles, two preloads and two guards.
 *
 * Split out of `functions.php` at 392 of the 400-line ceiling, and the seam is the same one
 * `inc/content.php` was cut on: what is left there SETS THE THEME UP - supports, menus,
 * starter content, the attributes on `<html>` - and everything here decides what a browser is
 * asked to fetch and in what order.
 *
 * THE ORDER IS LOAD-BEARING and it is [invariant 1]: base, then tokens, then bridge, wired by
 * dependency rather than by the order of the calls, because WordPress emits by dependency.
 * `check:order` reads this file and `functions.php` together, so moving a call from one to
 * the other cannot dodge it.
 *
 * @package QuireInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The sheets and the two reader bundles.
 *
 * The bundles are Quire Ink's own, copied by tools/extract.ts: `core` is the chrome (palette
 * switch, rail, search overlay, back-to-top) and `post` is the article (table-of-contents
 * scrollspy, book mode, lightbox, quote copy, resume). Both are IIFEs - the engine builds them
 * that way so two self-contained bundles cannot stamp on each other's globals - and they are
 * plain deferred scripts here for the reason written beside the call.
 */
function quireink_assets() {
	$dir = get_template_directory_uri();
	$v   = QUIREINK_VERSION;

	wp_enqueue_style( 'quireink-base', $dir . '/assets/css/quireink-base.css', array(), $v );

	/*
	 * The IDE chrome is the one part of the look an owner can switch off, so switching it off
	 * stops it being DOWNLOADED rather than merely stopping it applying: 5,652 B of gzip that
	 * a reader no longer pays for a treatment the site has decided against. Left on - which is
	 * the default - it costs 839 B of gzip over the single sheet, because the same bytes
	 * compress a little worse in two files, plus one request on an open connection.
	 *
	 * Where it lands among the sheets does not matter, and that is a property of the sheet
	 * rather than luck: every selector in it carries `html[data-look=code]`, so it cannot
	 * tie with anything else the theme loads. It is put here because this is where it sat
	 * inside the base sheet, and a reader of this list should not have to wonder.
	 */
	if ( 'on' === get_theme_mod( 'quireink_ide_chrome', 'on' ) ) {
		wp_enqueue_style( 'quireink-look-code', $dir . '/assets/css/quireink-look-code.css', array( 'quireink-base' ), $v );
	}

	wp_enqueue_style( 'quireink-tokens', $dir . '/assets/css/quireink-tokens.css', array( 'quireink-base' ), $v );
	wp_enqueue_style( 'quireink-bridge', $dir . '/assets/css/bridge.css', array( 'quireink-tokens' ), $v );

	// style.css carries the theme header and no rules; WordPress still expects the handle to
	// exist, and a child theme's own style.css depends on it.
	wp_enqueue_style( 'quireink-style', get_stylesheet_uri(), array( 'quireink-bridge' ), $v );

	/*
	 * PLAIN DEFERRED SCRIPTS, not modules. They carried `type="module"` through 0.1.3, added
	 * by a `script_loader_tag` filter whose stated reason was that the bundles are built
	 * `format: esm` and open with a top-level `import`. They are not and they do not: the
	 * blog engine builds both as an IIFE and has since before this theme existed, precisely
	 * so that two self-contained bundles cannot stamp on each other's globals.
	 *
	 * What the attribute did buy was a CORS requirement. A module is fetched in CORS mode
	 * whatever the origin, so a site serving `wp-content` from a CDN that does not send
	 * `Access-Control-Allow-Origin` lost every line of the theme's JavaScript - no palette
	 * switch, no search, no book mode, no table of contents - and nothing in the page said
	 * why. That is also the shape of the bug that made every screenshot in this repository
	 * for three months a page with no behaviour and a fallback face.
	 */
	wp_enqueue_script( 'quireink-core', $dir . '/assets/js/core.js', array(), $v, array( 'strategy' => 'defer', 'in_footer' => false ) );
	/*
	 * BOTH OF THESE ARE 'before', AND THE POSITION IS THE POINT.
	 *
	 * `WP_Scripts::filter_eligible_strategies()` says it in one line: "Handles with inline
	 * scripts attached in the 'after' position cannot be delayed." The drawer guard was
	 * attached 'after', so `'strategy' => 'defer'` above was being asked for and silently
	 * refused, and `core.js` has been a BLOCKING script in the head of every page since the
	 * guard was written - 12 KB that HTML parsing waits for, on a theme whose whole argument
	 * is what a page costs a reader. Measured on the local stack: `script.defer` false with
	 * the guard 'after', true with it 'before'.
	 *
	 * Neither script needs core.js to have run. The beacon guard only replaces a method that
	 * core.js will later call, so it has to be FIRST; the drawer guard waits for
	 * DOMContentLoaded before it reads an element, so where it sits among the scripts in the
	 * head changes nothing about when it runs.
	 */
	wp_add_inline_script( 'quireink-core', quireink_no_beacon_js(), 'before' );
	wp_add_inline_script( 'quireink-core', quireink_rail_inert_js(), 'before' );

	if ( is_singular() ) {
		wp_enqueue_script( 'quireink-post', $dir . '/assets/js/post.js', array( 'quireink-core' ), $v, array( 'strategy' => 'defer', 'in_footer' => false ) );

		/*
		 * BOOK MODE IS ITS OWN BUNDLE, and it has to be enqueued or the buttons on an article
		 * do nothing. It used to live inside post.js; the blog engine split it out on
		 * 2026-09-06 because it was 7.8 KB of post.js's 19.6 sent to every reader of a site
		 * with the feature off. The engine emits the tag only when its own switch is on, so
		 * this is the same switch, and switching it off stops the download rather than merely
		 * hiding the button - the same bargain `quireink-look-code` makes with its sheet.
		 */
		if ( quireink_book_mode() ) {
			wp_enqueue_script( 'quireink-book', $dir . '/assets/js/book-mode.js', array( 'quireink-post' ), $v, array( 'strategy' => 'defer', 'in_footer' => false ) );
		}
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'quireink_assets' );

/**
 * The faces the first screenful is set in, fetched before the sheet asks for them.
 *
 * WHAT IT FIXES. Every face declares `font-display:swap`, so with no preload the first paint
 * is a fallback and the real face swaps in when the sheet has been parsed, the `@font-face`
 * matched and the file fetched - one round trip after the CSS. The reading face is the text
 * the page is judged by and a monospace furniture face re-flows the header, the meta line and
 * both rails when it lands.
 *
 * WHICH FILES, AND WHY NOT ALL OF THEM. `quireink_font_preload()` is generated by running the
 * blog engine's own `fontPreloadHrefs()` once per pair of faces and once per language - see
 * `tools/appearance-php.ts`. It is the engine's rule rather than this theme's, and the rule
 * has a price written on it upstream: preloading a chrome face a default install barely paints
 * a glyph in cost 160ms of LCP when it shipped there. A furniture face whose fallback is
 * metric-compatible is therefore NOT preloaded, and a site in a language none of the bundled
 * faces covers gets no preload at all.
 *
 * Twenty-one files ship, a browser fetches four, and this asks early for the two it cannot
 * paint the first screen without.
 *
 * @param array $resources Resources WordPress will print as `<link rel="preload">`.
 * @return array
 */
function quireink_preload_fonts( $resources ) {
	$map = quireink_font_preload();
	$key = get_theme_mod( 'quireink_font', 'literata' ) . '|' . get_theme_mod( 'quireink_chrome_font', 'jetbrains-mono' );
	if ( ! isset( $map[ $key ] ) ) {
		return $resources;
	}

	// `get_locale()` is `vi`, `en_US`, `zh_CN`; the table is keyed by the language alone.
	$lang  = strtolower( substr( get_locale(), 0, 2 ) );
	$files = isset( $map[ $key ][ $lang ] ) ? $map[ $key ][ $lang ] : $map[ $key ]['*'];
	$dir   = get_template_directory_uri() . '/assets/fonts/';

	foreach ( $files as $file ) {
		$resources[] = array(
			// A font is fetched in CORS mode whatever the origin, so a preload without this
			// is a second, separate fetch of the same file rather than a warm cache.
			'href'        => $dir . $file,
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}

	return $resources;
}
add_filter( 'wp_preload_resources', 'quireink_preload_fonts' );

/**
 * The article's typography, inside the editor canvas.
 *
 * THE PROBLEM. Everything a reader sees inside a post is scoped to `.prose`: the reading face,
 * the measure, the heading scale, the list indents, the blockquote rule. The editor canvas is a
 * bare `.editor-styles-wrapper` and has no such class, so none of it applied - an author wrote
 * in the mono chrome face at one width and published in a book serif at another. Every static
 * check passed the whole time, because nothing about the PAGE was wrong.
 *
 * `tools/editor-css.ts` generates those same rules addressed at `body`, which inside the
 * iframed canvas is the wrapper itself.
 *
 * WHY NOT `add_editor_style()`. It was tried, it registers cleanly, `get_editor_stylesheets()`
 * lists it, the editor settings carry its text - and the canvas never receives it, while the
 * three sheets beside it in the same call all arrive. Rather than keep guessing at what the
 * editor does to a sheet on its way in, this enqueues the file. Since 6.3 the canvas is an
 * iframe and `enqueue_block_assets` runs inside it, so the file lands as a plain stylesheet
 * with nothing rewriting it.
 *
 * `is_admin()` because that hook fires on the front end too, where these rules would be a
 * second copy of the article's typography aimed at an element that is not there.
 */
function quireink_editor_css() {
	if ( ! is_admin() ) {
		return;
	}
	/*
	 * No dependency, because ordering cannot help: the editor injects its own `<style>` blocks
	 * after every enqueued link, so a link is never last. The generated sheet wins its ties on
	 * weight instead - see tools/editor-css.ts for why that is safe against the author's own
	 * choices and only aimed at WordPress's defaults.
	 */
	wp_enqueue_style(
		'quireink-editor',
		get_template_directory_uri() . '/assets/css/editor.css',
		array(),
		QUIREINK_VERSION
	);
}
add_action( 'enqueue_block_assets', 'quireink_editor_css' );

/**
 * Keep the closed drawer out of the tab order.
 *
 * THE PROBLEM THIS ANSWERS. The rail is printed before `<main>` so that a keyboard reaches
 * the sidebar where a reader sees it - in the left gutter, beside the first line. Above the
 * rail breakpoint that is exactly right. Below it the same element is a drawer parked at
 * `translateX(-100%)`, off the left edge of the screen and opened from the header's menu
 * button - and a translated element is still focusable, so on a phone the first eight Tab
 * presses after the header went to links nobody could see, before the article was reached.
 *
 * `inert` is the whole fix: it takes the subtree out of the tab order AND out of the
 * accessibility tree, which is what an off-screen drawer should be, and gives it all back
 * the moment the drawer opens.
 *
 * WHY THIS IS NOT CSS. The honest test is `visibility:hidden` on the closed drawer, undone
 * above the breakpoint - and the breakpoint is COMPUTED from the reading column by the blog
 * engine, emitted into the generated sheet, and cannot be read by a media query written here.
 * Writing the number into `bridge.css` would be the one thing invariant 3 exists to prevent.
 * Reading `position` back off the element asks the sheet what it actually decided, at
 * whatever width, without this file knowing the number at all: `fixed` is the drawer,
 * `absolute` is the gutter.
 *
 * With JavaScript off the drawer is focusable, which is where it was before. That is the
 * shape of an enhancement rather than a fix that can fail closed.
 *
 * @return string
 */
function quireink_rail_inert_js() {
	return <<<'JS'
(function(){var run=function(){var rails=document.querySelectorAll('.rail');if(!rails.length||!('inert' in rails[0]))return;var html=document.documentElement;var sync=function(){for(var i=0;i<rails.length;i++){rails[i].inert=getComputedStyle(rails[i]).position==='fixed'&&html.dataset.rail!=='open';}};sync();addEventListener('resize',sync,{passive:true});new MutationObserver(sync).observe(html,{attributes:true,attributeFilter:['data-rail']});};if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',run);}else{run();}})();
JS;
}

/**
 * Refuse the one call in the copied bundle that nobody asked for.
 *
 * `core.js` carries Quire Ink's analytics beacon (`src/assets/js/track.ts`). It is not gated
 * on a setting and it needs no interaction: on load it POSTs the path, the referring host and
 * whether the device has a touch screen to `/api/track`, and on the way out it posts scroll
 * depth, engaged time and the bytes the visit pulled. On a Quire Ink blog that is the owner's
 * own analytics; here the route does not exist, so every page view has been answering a 404 -
 * twice, counting the leave beacon - on every site running this theme since 0.1.0.
 *
 * It is answered rather than left, where the newsletter form and the comment thread are left
 * (see `tools/extract.ts`), and the difference is that those two fail quietly when a reader
 * presses a button while this one fires by itself and contradicts the first claim in the
 * theme's own description. "No analytics" has to be true of the browser's outbox, not only of
 * the server's disk.
 *
 * NARROW ON PURPOSE. It wraps `navigator.sendBeacon` and drops exactly one URL; every other
 * call, from any plugin, is handed straight through to the original. A plugin's own analytics
 * is that site's business.
 *
 * WHY NOT EDIT THE BUNDLE. It is copied verbatim so that the reader behaviour is the product's
 * rather than a fork of it, and `check:generated` compares bytes. The real answer is a build
 * flag or a `data-` switch upstream, which is where this belongs and where it is raised - the
 * same shape of item as the OFL file and the form-field hairline before it.
 *
 * @return string
 */
function quireink_no_beacon_js() {
	return <<<'JS'
(function(){var s=navigator.sendBeacon;if(!s)return;navigator.sendBeacon=function(u,d){return String(u).indexOf('/api/track')===0?true:s.call(navigator,u,d);};})();
JS;
}
