<?php

if ( !empty( $ad['key'] ) ) {
	$ad['key'] = '/' . $ad['key'];
}

$ad_url = sprintf( '//ad.mo.doubleclick.net/DARTProxy/mobile.handler?k=%s/%s/%s;c=it;key=%s;forecast=1;',
	$ad['key'],
	$ad['sitename'],
	$ad['zone'],
	$provider->format_params( $params ) );

/**
 * BlackBerry devices don't interpret document.write('<script></script>'); calls, they won't execute the script that gets written to the DOM.  So to be compatible with BlackBerry devices, we'll only refresh the $ord when the cache refreshes instead of using JS.  Cleared with Cham.
 * @since   2010-12-27 Gabriel Koen
 * @version 2010-12-27 Gabriel Koen
 */
$ad_url .= 'ord=' . time() . ';';
$ad_url .= '&dw=1';

?>
<div style="<?php echo esc_attr( $ad['css-style'] ); ?>"
	class="pmc-adm-js-div <?php echo esc_attr( $ad['css-class'] ); ?>">
	<script type="text/javascript" src="<?php echo esc_url( $ad_url ); ?>"></script>
</div>

<!-- End ad tag -->
