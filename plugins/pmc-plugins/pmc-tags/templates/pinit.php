<?php
// Ignoring coverage temporarily 
// @codeCoverageIgnoreStart
if ( is_single() ) :
	$blocker_atts = [
		'type'  => 'text/javascript',
		'class' => '',
	];
	
	if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
		$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
	}
// @codeCoverageIgnoreEnd
?>
	<!-- get credit in Pinterest's algorithm for user sharing -->
	<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>" src="//assets.pinterest.com/js/pinit.js" async defer></script>
<?php endif; ?>
