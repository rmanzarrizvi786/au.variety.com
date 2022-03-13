<?php

if ( !empty( $ad['targeting_data'] ) ) {
	foreach ( $ad['targeting_data'] as $item ) {
		if ( empty( $item['key'] ) || empty( $item['value'] ) || !empty( $params[ $item['key'] ] ) ) {
			continue;
		}
		$params[ $item['key'] ] = $item['value'];
	}
}

$ad_url = sprintf( '//ad.doubleclick.net/%s/%s/%s/%s;%s',
	$ad['key'],
	$ad['type'],
	$ad['sitename'],
	$ad['zone'],
	$provider->format_params( $params ) );

$ad_url .= str_replace( '.', '', microtime( true ) ) . '?';

$image_src = sprintf( '//ad.doubleclick.net/%s/ad/%s/%s;%s', $ad['key'], $ad['sitename'], $ad['zone'], $provider->format_params( $params ) );

if ( 'adj' === $ad['type'] ) {

	?>
	<div style="<?php echo esc_attr( $ad['css-style'] ); ?>"
		class="pmc-adm-js-div <?php echo esc_attr( $ad['css-class'] ); ?>">
		<script type="text/javascript" src="<?php echo esc_url( $ad_url ); ?>"></script>
	</div>

<?php
} else {

	?>
	<!-- begin ad tag -->
	<div style="<?php echo esc_attr( $ad['css-style'] ); ?>"
		class="pmc-adm-iframe-div" data-adclass="<?php echo esc_attr( $ad['css-class'] ); ?>"
		 data-adurl="<?php echo esc_attr( $ad_url ); ?>"
		 data-device="<?php echo esc_attr( $ad['device'] ); ?>"
		 data-adheight="<?php echo esc_attr( $ad['height'] ); ?>"
		 data-adwidth="<?php echo esc_attr( $ad['width'] ); ?>">
	</div>
<?php
}
?>
<?php
/*
 * Don't render no script tag other then desktop since our other device rendering is based on javascript width detection.
 */
if ( 'desktop' === $ad['device'] ):
?>
	<noscript>
		<a href="<?php echo esc_url( $image_src ); ?>" target="_blank">
			<img src="<?php echo esc_url( $image_src ); ?>" width="<?php echo esc_attr( $ad['width'] ); ?>px" height="<?php echo esc_attr( $ad['height'] ); ?>px" border="0" alt="">
		</a>
	</noscript>
<?php endif; ?>
<!-- End ad tag -->
