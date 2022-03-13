/**
 * U.S. Privacy IAB API implementation
 */
window.__uspapi = new function( win ) {
	// global vars
	const USPSTR_NOT_OPTED_OUT_SALE = '1YNY'; //Default California NOT opted out user.
	const USPSTR_OPTED_OUT_SALE = '1YYY'; // Californian user opted out for sale of Information.
	const USPSTR_NA = '1---'; // Not Applicable - When no cookie found and user is not from California.
	const API_VERSION = 1;
	const validStringRegExp = /^[1][nNyY-][nNyY-][nNyY-]$/;
	var pendingCalls = [];

	if ( win.__uspapi ) {
		try {
			// if the api was already loaded, then use it
			if ( win.__uspapi( '__uspapi' ) ) {
				return win.__uspapi;
			} else {
				// Making a call to __uspapi with no arguments will return the pending calls;
				pendingCalls = win.__uspapi() || [];
			}
		} catch ( nfe ) {
			return win.__uspapi;
		}
	}

	var api = function ( cmd ) {
		try {
			return {
				getUSPData: get_uspdata,
				__uspapi: function () {
					return true;
				}
			} [cmd].apply( null, [].slice.call( arguments, 1 ) );
		}
		catch (err) {
			console.error( '__uspapi: Invalid command: ', cmd );
		}
	};

	var get_cookie = function( cookie_name ) {
		var name = cookie_name + '=';
		var cookiearray = document.cookie.split( ';' );
		for (var i = 0; i < cookiearray.length; i++) {
			var cookie = cookiearray[i];
			while ( cookie.charAt( 0 ) == ' ' ) {
				cookie = cookie.substring( 1 );
			}
			if ( cookie.indexOf( name ) == 0 ) {
				return cookie.substring( name.length, cookie.length );
			}
		}
		return '';
	};

	var set_cookie = function( cookie_name ) {
		var cookie_date  = new Date();
		var cookie_value = Date.now() + '-N';
		cookie_date.setFullYear( cookie_date.getFullYear() + 10 ); // set for 10 yrs
		document.cookie = cookie_name + '=' + cookie_value + ';expires=' + cookie_date + ';path=/;secure';
	};

	var get_uspdata = function( apiver, callback ) {

		if ( typeof callback === 'function' ) {
			if (
				apiver !== null &&
				apiver !== undefined &&
				apiver != API_VERSION
			) {
				if ( typeof callback === 'function' )
					callback( null, false );
				return;
			}

			var ccpa_str = null,
				user_region = '',
				pmc_usprivacy_str ='',
				pmc_usprivacy = get_cookie( 'pmc_usprivacy' );

			if ( 'object' === typeof pmc_fastly_geo_data && 'string' === typeof pmc_fastly_geo_data.region ) {
				user_region = pmc_fastly_geo_data.region;
			}

			//Check for cookie 'pmc_usprivacy' ex: 32432432453-Y
			if ( 'string' === typeof pmc_usprivacy && pmc_usprivacy.length > 0 ) {

				pmc_usprivacy_str = pmc_usprivacy[ pmc_usprivacy.length - 1 ];

				if ( 'y' === pmc_usprivacy_str.toLowerCase() ) {
					ccpa_str = USPSTR_OPTED_OUT_SALE;
				} else if ( 'n' === pmc_usprivacy_str.toLowerCase() ) {
					ccpa_str = USPSTR_NOT_OPTED_OUT_SALE;
				}

			} else {

				if ( 'ca' === user_region.toLowerCase() ) {
					ccpa_str = USPSTR_NOT_OPTED_OUT_SALE;
					//set cookie as not opted out
					set_cookie( 'pmc_usprivacy' );

				} else {
					ccpa_str = USPSTR_NA; // setting as Not applicable since there is no cookie & not in CA
				}
			}

			if ( ! validStringRegExp.test( ccpa_str ) ) {
				ccpa_str = null;
			}

			if ( ccpa_str ) {
				callback(
					{
						version: API_VERSION,
						uspString: ccpa_str
					},
					true
				);
			} else {
				callback(
					{
						version: API_VERSION,
						uspString: null
					},
					false
				);
			}
		} else {
			console.error( '__uspapi: callback parameter not a function' );
		}
	};

	// Add "__uspapiLocator" frame to the window
	function add_ccpa_iframe() { // creates ot frame
		if ( ! window.frames['__uspapiLocator'] ) {
			if ( document.body ) {
				const iframe = document.createElement( 'iframe' );
				iframe.style.cssText = 'display:none';
				iframe.name = '__uspapiLocator';
				document.body.appendChild( iframe );
			} else {
				setTimeout( add_ccpa_iframe, 5 );
			}
		}
	};
	add_ccpa_iframe();

	// register postMessage handler
	function __handleUspapiMessage ( event ) {
		const data = ( event && event.data && event.data.__uspapiCall ) ? event.data : '';
		if ( data ) {
			window.__uspapi( data.command, data.version, function( returnValue, success ) {
				event.source.postMessage( {
					__uspapiReturn: {
						returnValue,
						success,
						callId: data.callId
					}
				}, '*');
			} );
		}
	};

	window.addEventListener( 'message', __handleUspapiMessage, false );

	return api;
} (window);
