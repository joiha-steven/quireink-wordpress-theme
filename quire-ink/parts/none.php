<?php
/**
 * Nothing matched.
 *
 * `.empty` is the blog engine's name for this line and carries its colour. The earlier
 * markup here wore `t-small text-meta` inside an `article.reveal`, which looked right and
 * reached none of the sheet's own rule for the state.
 *
 * A failed SEARCH and an empty archive are not the same sentence. "No posts here yet" told a
 * reader whose query matched nothing that the whole blog was empty.
 *
 * @package QuireInk
 */

?>
<p class="empty">
<?php
if ( is_search() ) {
	esc_html_e( 'Nothing matched that search.', 'quire-ink' );
} else {
	esc_html_e( 'No posts here yet.', 'quire-ink' );
}
?>
</p>
