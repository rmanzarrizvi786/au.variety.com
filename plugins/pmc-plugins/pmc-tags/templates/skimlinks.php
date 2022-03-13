<?php
/**
 * Template to add skimlink js.
 */

$domain_id = apply_filters( 'pmc_tags_skimlink_domain_id', '87443X1540250' );

if ( empty( $domain_id ) || ! is_string( $domain_id ) ) {
	// no Skimlinks domain ID set
	// bail out
	return;
}

$url = sprintf(
	'https://s.skimresources.com/js/%s.skimlinks.js',
	$domain_id
);

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

<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>" src="<?php echo esc_url( $url ); ?>" async></script>
