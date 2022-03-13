 /* global pmc, ga, pmc_google_content_experiment */

( function ( window ) {

 var init_pmc_gce = function() {
 	var experiment_data = {};
 
 	// Grab the current experiment data
 	try {
		experiment_data = pmc_google_content_experiment;
	} catch ( e ) {
 		// do nothing
	}
 
	/**
	 * Set a cookie to ensure users continually receive the
	 * same variations for the current experiment.
	 *
	 * These are set with JS because our PHP code is executed too late
	 * to set headers (it must run on 'wp' action).
	 *
	 * Example cookie value for user participating in multiple experiments:
	 * Aa2rX5sfTIq0jVH9Va3V0A==3|CQgGxYBQTsmCxoTo7LlUCA==1|wYdqtPuLT-yAaUqoYXJUnA==4
	 */
 	try {
		var cookie_name = 'pmc_google_content_experiments',
			cookie_experiments = [],
			new_cookie_experiments = [],
			add_experiment_to_cookie = true,
			update_cookie = false;
		
		// Check if we need to add to/update/or remove any experiments from the cookie
		var existing_cookie_value = pmc.cookie.get( cookie_name );

		if ( ! pmc.is_empty( existing_cookie_value ) ) {
			cookie_experiments = existing_cookie_value.split( '|' );

			if ( ! pmc.is_empty( cookie_experiments ) ) {
				var i = cookie_experiments.length;

				while ( i-- ) {
					var cookie_experiment_data = cookie_experiments[ i ].split( '==' );
					
					// Clean up/remove any now-disabled experiments from the user cookie
					if ( ! pmc.is_empty( experiment_data.disabled_experiments ) ) {
						if ( -1 !== experiment_data.disabled_experiments.indexOf( cookie_experiment_data[0] ) ) {
							cookie_experiments.splice( i, 1 );
							update_cookie = true;
							continue;
						}
					}
					
					// Don't add the experiment if it's already in the cookie
					if ( cookie_experiment_data[0] === experiment_data.experiment_id ) {
						add_experiment_to_cookie = false;
					}
				}
			}
		}

		// Add the experiment to the cookie if it's not already in there
		if ( add_experiment_to_cookie ) {
			cookie_experiments.push( [ experiment_data.experiment_id, experiment_data.experiment_variation ].join( '==' ) );
			update_cookie = true;
		}

		// Update the cookie if we need to
		if ( update_cookie ) {
			pmc.cookie.set(
				cookie_name,
				cookie_experiments.join( '|' ),
				
				// Use 30 days as an arbitrary expiration date.
				// No experiment should run that long, but just to be safe.
				60 * 60 * 24 * 30,
				'/'
			);
		}
	} catch ( e ) {
		// do nothing
	}

	/**
	 * Run the chosen variation callback function if there is one.
	 * Some experiments may not have an accompanying JS file.
	 *
	 * window.PMCCXPageVariations is defined within the currently-enabled experiment JS
	 */
	try {
		window.PMCCXPageVariations[ experiment_data.experiment_variation ]( jQuery );
	} catch ( e ) {
		// do nothing
	}
 }

 if ( window.addEventListener ) {
	 window.addEventListener( 'load', init_pmc_gce );
 } else {
	 window.attachEvent( 'onload', init_pmc_gce );
 }
} ( window ) );
