<?php
/**
 * Cxense tags to render early in head.
 */

?>
<!-- Cxense tags -->
<link rel="preconnect" href="https://scdn.cxense.com" crossorigin>
<link rel="dns-prefetch" href="//scdn.cxense.com" />

<?php
// Render the meta tags to be loaded in the <head> of html
if ( isset( $meta_tags ) && isset( $allowed_html ) ) {
	echo wp_kses( $meta_tags, $allowed_html );
}
