<?php
/**
 * Loading IAS header script in the header tags.
 */

$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];

if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
}
?>
<!-- Start: IAS script -->
<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>" src='https://static.adsafeprotected.com/vans-adapter-google-ima.js'></script>
<!-- End: IAS script -->
