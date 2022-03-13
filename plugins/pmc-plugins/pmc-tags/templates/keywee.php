<?php
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

<script src="<?php echo esc_url( sprintf( '//dc8xl0ndzn2cb.cloudfront.net/js/%s/v0/keywee.min.js', $option['values']['id'] ) );?>" type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>" async></script>
