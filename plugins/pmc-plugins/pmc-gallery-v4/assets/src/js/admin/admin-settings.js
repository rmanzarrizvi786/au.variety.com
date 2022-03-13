jQuery( document ).ready( function () {
	var enable_selector = jQuery( "#enable_interstitial" );
	var enable_interstitial = enable_selector.prop( "checked" );
	var interstitial_selectors = jQuery( "#interstitial_refresh_clicks, #interstitial_duration, #start_with_interstitial, #interstitial_ad_code" );

	interstitial_selectors.each( function () {
		var _this = jQuery( this );
		if ( enable_interstitial ) {
			_this.closest( "tr" ).css( 'display', 'table-row' );
		} else {
			_this.closest( "tr" ).css( 'display', 'none' );
		}
	} );

	enable_selector.change( function () {
		// Get current value of enable_interstitial
		var enable_interstitial = enable_selector.prop( "checked" );
		interstitial_selectors.each( function () {
			var _this = jQuery( this );
			if ( enable_interstitial ) {
				_this.closest( "tr" ).css( 'display', 'table-row' );
			} else {
				_this.closest( "tr" ).css( 'display', 'none' );
			}
		} );
	} );

} );

//EOF
