<!-- Venatus Ad Manager -->
<?php
// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

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

<script id="pmcTagsVenatus" type="<?php echo esc_attr( $blocker_atts['type'] ); ?>">
	(function(){
		function pmcLoadVenatus() {
			var container     = document.getElementById( 'pmcTagsVenatus' ).parentElement;
			var venatusScript = document.createElement('script');

			venatusScript.setAttribute('type', '<?php echo esc_attr( $blocker_atts['type'] ); ?>');
			venatusScript.setAttribute('class', '<?php echo esc_attr( $blocker_atts['class'] ); ?>');
			venatusScript.setAttribute('src', 'https://hb.vntsm.com/v3/live/ad-manager.min.js');
			venatusScript.setAttribute('data-site-id','<?php echo esc_attr( $option['values']['site_id'] ); ?>');
			venatusScript.setAttribute('data-mode','scan');
			venatusScript.setAttribute('async','async');

			container.appendChild( venatusScript );
		}

		var scrollSubscriber = document.cookie.indexOf("scroll0=") > -1;

		if ( ! scrollSubscriber ) {
			pmcLoadVenatus();
		}
	})();
</script>
<!-- / Venatus Ad Manager -->
