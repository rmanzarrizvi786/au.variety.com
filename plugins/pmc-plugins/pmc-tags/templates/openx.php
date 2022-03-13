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
<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>" src="//pmc-d.openx.net/w/1.0/jstag?nc=3782-PMC RON"></script>
