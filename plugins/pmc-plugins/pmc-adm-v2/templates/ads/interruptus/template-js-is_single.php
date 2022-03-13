<script type="text/javascript" class="script-mobile">
function pmc_ads_interruptus_single() {
	if ( typeof pmc == 'undefined' || ! pmc ) {
		return;
	}

	var home_url = "<?php echo esc_js( $home_url ); ?>";
	var endpoints = ["<?php echo implode( '", "', array_map( 'esc_js', $endpoints ) ); ?>"];
	var current_url = window.location.href;

	for ( var i = 0; i < endpoints.length; i++ ) {
		if ( pmc.is_empty( endpoints[ i ] ) ) {
			continue;
		}

		var endpoint = endpoints[ i ].toLowerCase();

		//check cookie
		var yummy_cookie = pmc.cookie.get( endpoint );

		if ( ! pmc.is_empty( yummy_cookie ) ) {
			//cookie is set, skip to next endpoint
			continue;
		}

		//cookie not set, save current_url in cookie
		pmc.cookie.set( 'pmc_interrupted_url', encodeURIComponent( current_url ), 300, '/' );

		// no cookie
		if ( '' == pmc.cookie.get( 'pmc_interrupted_url', '' ) ) {
			// PPT-3389 - disable ad if browser not supporting cookie.
			return;
		}

		//redirect to endpoint url
		window.location.href = home_url + "/" + endpoint + "/";

		//exit this loop
		break;
	}
}

pmc_ads_interruptus_single();
</script>
