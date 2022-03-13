<?php
// Ignoring coverage temporarily 
// @codeCoverageIgnoreStart
$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];

if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'script-mobile optanon-category-C0004' );
}
// @codeCoverageIgnoreEnd
?>

<?php if ( is_home() || ( is_single() && 'post' === get_post_type() ) ) : ?>
	<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>" async src="https://load.instinctiveads.com/i.js"></script>
<?php endif; ?>
