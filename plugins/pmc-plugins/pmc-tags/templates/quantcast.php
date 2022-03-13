<?php
// Ignoring coverage temporarily
// @codeCoverageIgnoreStart
$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];
$option['values']['sitepcode'] = ( ! empty( $option['values']['sitepcode'] ) ) ? $option['values']['sitepcode']  : '';

if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0002' );
}
// @codeCoverageIgnoreEnd
?>
<?php if ( 'top' === $position ) : ?>
	<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>"> var _qevents = _qevents || []; (function() { var elem = document.createElement('script'); elem.src = (document.location.protocol == "https:" ? "https://secure" : "http://edge") + ".quantserve.com/quant.js"; elem.async = true; elem.type = "text/javascript"; var scpt = document.getElementsByTagName('script')[0]; scpt.parentNode.insertBefore(elem, scpt); })(); </script>
<?php endif; ?>

<?php if ( 'bottom' === $position ) : ?>
	<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
		<?php
		if (
			! empty( $option['values']['sitepcode'] ) && ! empty( $option['values']['pmcpcode'] )
			&& $option['values']['sitepcode'] !== $option['values']['pmcpcode']
		) {
		?>
		_qevents.push( [
			{ qacct : <?php echo wp_json_encode( $option['values']['pmcpcode'] ); ?> }, // Blog network operator
			{ qacct : <?php echo wp_json_encode( $option['values']['sitepcode'] ); ?> } // Individual blog/brand
		] );
		<?php
		} elseif ( ! empty( $option['values']['pmcpcode'] ) ) {

			printf(
				'_qevents.push( { qacct:%s } ); // Blog network operator',
				wp_json_encode( $option['values']['pmcpcode'] )
			);

		}
		?>
	</script>
	<noscript>
		<div style="display:none;">
		<img src="<?php echo esc_url( '//pixel.quantserve.com/pixel?a.1=' . $option['values']['sitepcode'] . '&a.2=' . $option['values']['pmcpcode'] ); ?>" border="0" height="1" width="1" alt="Quantcast"/>
		</div>
	</noscript>
<?php endif; ?>
