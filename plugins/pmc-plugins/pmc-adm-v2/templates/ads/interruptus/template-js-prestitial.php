<script type="text/javascript" class="script-mobile">
var url_to_go_to = '';
var ad_display_duration = <?php echo ( ! empty( $duration ) ) ? intval( $duration ) : 8; ?>;

function pmc_ads_interruptus_back_to_site() {
	if ( pmc.is_empty( url_to_go_to ) ) {
		return;
	}

	if ( window.history.length > 0 ) {
		window.history.go( url_to_go_to );
	}

	window.location.replace( url_to_go_to );
}

function pmc_ads_interrupted_prestitial() {
	if ( typeof pmc == 'undefined' || ! pmc ) {
		return;
	}

	var home_url = "<?php echo esc_js( $home_url ); ?>";

	//check cookie
	url_to_go_to = pmc.cookie.get( 'pmc_interrupted_url' );

	if ( ! pmc.is_empty( url_to_go_to ) ) {
		url_to_go_to = decodeURIComponent( url_to_go_to );
	} else {
		url_to_go_to = home_url;
	}

	//set endpoint cookie
	pmc.cookie.set( 'prestitial', 'viewed', <?php echo intval( $time_gap ); ?>, '/' );

	//remove url cookie
	pmc.cookie.expire( 'pmc_interrupted_url', '/' );

	try {
		if ( typeof window.history.replaceState === 'function' ) {
			window.history.replaceState( window.history.state, '', url_to_go_to );
		}
	} catch ( err ) {}

}

pmc_ads_interrupted_prestitial();
</script>
