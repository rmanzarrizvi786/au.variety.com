<?php
/**
 * Template to add Trackonomics js.
 */

$url = sprintf( 'https://cdn-magiclinks.trackonomics.net/client/static/v2/%s.js', $option['values']['customer_id'] );

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

<!--START Trackonomics Tag-->
<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>" id="funnel-relay-installer" data-property-id="PROPERTY_ID" data-customer-id="<?php echo esc_attr( $option['values']['customer_id'] ); ?>" src="<?php echo esc_url( $url ); ?>" async="async"></script>
<!--END Trackonomics Tag-->
