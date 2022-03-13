<?php
/**
 * Template for habu.com script
 * Habu:: Data Accelerator Widget
 */
if ( ! empty( $option['values']['id'] ) ) {
	// Ignoring coverage temporarily 
	// @codeCoverageIgnoreStart
	$blocker_atts = [
		'type'  => 'text/javascript',
		'class' => '',
	];

	if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
		$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
	}
	// @codeCoverageIgnoreEnd
	?>

	<!--START Habu.js Tag Deployment-->
	<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>"
			src="<?php echo esc_url( sprintf( 'https://cdn.imhd.io/quarterdeck/%s/habu.js', $option['values']['id'] ) ); ?>"></script>
	<!--END Habu.js Tag Deployment-->

	<?php
}
