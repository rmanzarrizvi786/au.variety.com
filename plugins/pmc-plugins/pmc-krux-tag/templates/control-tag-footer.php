<?php
if ( empty( $krux_config_id ) ) {
	return;
}

$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];

if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
}
?>

<!-- pmc-adm targeting -->
<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
if ( typeof pmc !== 'undefined' && typeof pmc.hooks !== 'undefined' ) {
	pmc.hooks.add_filter( 'pmc-adm-set-targeting-keywords', function( keywords ) {
		try {
			if ( typeof Krux !== 'undefined' ) {
				if ( typeof keywords['ksg'] === 'undefined' ) {
					keywords['ksg']  = Krux.segments;
				}
				if ( typeof keywords['kuid'] === 'undefined' ) {
					keywords['kuid']  = Krux.user;
				}
			}
		} catch(e) {}
		return keywords;
	} );
}

(function() {
	if( window.hasOwnProperty('pmc_krux' ) ) {
		window.pmc_krux['destination-url'] = window.location.href;
		if( document.hasOwnProperty('referrer') && document.referrer.length ) {
			window.pmc_krux['referrer'] = document.referrer;
		}
		if( 'undefined' !== typeof pmc_meta && 'string' === typeof pmc_meta.omni_visit_id ) {
			window.pmc_krux['omni_visit_id'] = pmc_meta.omni_visit_id;
		}
	}
})();

</script>
