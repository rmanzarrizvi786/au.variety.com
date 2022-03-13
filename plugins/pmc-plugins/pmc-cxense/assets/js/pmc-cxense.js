/* eslint-disable */
/** @todo resolve the linting in this file in PMCS-4749 */
/**
 * Cxense script to render on page
 * This script sends data to Cxense for page views and modules that need to be loaded.
 * Page views need correct site id, custom parameters and in some cases (gallery and/or proxy pages), corrected URL
 * Modules need the div id to replace content with, the module id and  and in some cases (gallery and/or proxy pages), corrected URL
 *
 * @package pmc-cxense
 *
 */

var cX = window.cX = window.cX || {},
	cxpmc,
	cxense_check_dependencies;

// Are third party cookies supported in this browser?
var cookie3PSupported = null;

cxpmc = {

	custom_parameters: pmc_cxense_data.custom_parameters,

	initialized: false,

	is_proxied: false,

	modules: pmc_cxense_data.modules,

	page_location: pmc_cxense_data.page_location,

	paywall_module: pmc_cxense_data.paywall_module,

	paywall_parameters: pmc_cxense_data.paywall_parameters,

	site_id: pmc_cxense_data.site_id,

	/**
	 * Helper function for calling cX.CCE.run.
	 * If page is proxied, adds a parameter for sending the page with the correct domain.
	 * @param module
	 * @returns {[string, *]|[string, *, {context: {url: undefined}}]}
	 */
	get_module_run_parameters: function( module ) {
		if ( this.is_proxied ) {
			return [ 'run', module, { 'context': { 'url' : this.page_location } }] ;
		}

		return [ 'run', module ];
	},

	/**
	 * If page is proxied, adds the reverse proxy url for later use.
	 * If page location is already set, it's a gallery page.
	 * @returns {string}
	 */
	get_page_location: function() {
		if ( this.page_location.length ) {
			return pmc.reverse_proxy_url( this.page_location );
		}

		return pmc.reverse_proxy_url( window.location.href );
	},

	/**
	 * Helper function for calling cX.sendPageViewEvent.
	 * If page is proxied, adds parameter for sending reversed proxied url so Cxense gets page view for correct page.
	 * @returns {[string, {location: (undefined|string)}]|[string]}
	 */
	get_page_view_parameters: function() {
		if ( this.page_location.length ) {
			return [ 'sendPageViewEvent', null, null, { 'location':this.page_location } ];
		}
		return [ 'sendPageViewEvent' ];
	},

	/**
	 * Sets up the context parameters in the expected format for
	 * sending the paywall module request, including the url if needed.
	 */
	get_paywall_context_parameters: function() {
		if ( this.is_proxied ) {
			return [ 'run', this.paywall_module, { 'url': this.page_location } ];
		}

		return [ 'run', this.paywall_module ];
	},

	/**
	 * Initializes needed variables if the page is proxied.
	 */
	initialize: function() {
		var self = this;
		if ( window.pmc.is_proxied() ) {
			self.is_proxied = true;
			self.page_location = self.get_page_location();
		}

		self.set_user_default_data();

		//Check Uls session for Variety.
		if ( 'variety' === window.pmc_meta['lob'] ) {
			self.subscriber.check_uls_data();
		}

		window.pmc.subscription_v2.on_subscriber_data_loaded(function(subscriber_data) {
			self.subscriber.check_subscriber_data(subscriber_data);
			self.run();
			self.load_modules();
		}, function() {
			self.run();
			self.load_modules();
		});

		self.initialized = true;
	},

	/**
	 * Calls Cxense javascript and then whatever callbacks needed once it's loaded.
	 * @param cb
	 */
	load_cce: function( cb ) {
		var script = document.createElement( 'script' );

		script.type    = 'text/javascript';
		script.async   = 'async';
		script.src     = 'https://scdn.cxense.com/cx.cce.js';
		script.onload  = cb;
		script.onerror = function() {
			console.log( 'Error loading ' + script.src );
		};
		document.getElementsByTagName( 'head' )[0].appendChild( script );
	},

	/**
	 * Loads all the modules set from the theme after ensuring the div id exists on the page.
	 * At this point, we've ensured targetElementId is a key of module. See `get_modules()` within the Plugin class.
	 */
	load_modules: function() {
		if ( this.modules.length ) {
			this.modules.forEach(function (module) {
				if (document.getElementById(module.targetElementId)) {
					cX.CCE.callQueue.push(cxpmc.get_module_run_parameters(module));
				}
			});
		}

		if ( this.paywall_module.hasOwnProperty( 'widgetId') ) {
			this.load_paywall();
		}
	},

	/**
	 * Loads the paywall module if the div is present on the page.
	 * The callback checks to make sure the needed variables are not empty.
	 */
	load_paywall: function() {
		if ( document.getElementById( 'cx-paywall' ) ) {
			cX.CCE.callQueue.push( this.get_paywall_context_parameters() );
		}
	},

	/**
	 * Function for gallery pages to report additional page views.
	 * Load modules does not need to be called again.
	 */
	report_gallery_pv: function() {
		cX.callQueue.push( [ 'initializePage' ] );
		this.run();
	},

	/**
	 * Sends page view data to Cxense. Site ID must be set first.
	 * Any custom parameters must be sent before the page view event.
	 */
	run: function() {
		// Make sure the site id is defined
		if ( this.site_id.length ) {
			this.update_custom_parameters();

			cX.callQueue.push( [ 'setSiteId', this.site_id ] );

			// Set custom parameters if there are any defined
			if ( Object.keys( this.custom_parameters ).length ) {
				cX.callQueue.push( [ 'setCustomParameters', JSON.parse( JSON.stringify( this.custom_parameters ) ) ] );
			}

			cX.CCE.callQueue.push( this.get_page_view_parameters() );
		}
	},

	update_custom_parameters: function() {
		this.custom_parameters['pmc-logged-in'] = this.subscriber.logged_in;
		this.custom_parameters['pmc-subscriber-type'] = this.subscriber.subscriber_type;
		this.custom_parameters['pmc-concurrency_rest'] = this.subscriber.concurrency_restricted;

	},

	/**
	 * Set user default custom parameters.
	 */
	set_user_default_data: function() {
		this.custom_parameters['pmc_account_type'] = '';
		this.custom_parameters['pmc_campaign'] = '';
		this.custom_parameters['pmc_reg_date'] = '';
		this.custom_parameters['pmc_days_since_reg'] = '';
	},

	/**
	 * Return days diff with current date.
	 *
	 * @param date
	 * @returns {number}
	 */
	days_ago: function( date ) {

		var user_date   = new Date( date );
		var todays_date = new Date();

		return parseInt( ( todays_date - user_date ) / ( 1000 * 60 * 60 * 24 ), 10 ); // return days diff with current date.
	},

	subscriber: {
		logged_in: 'no',
		subscriber_type: 'free',
		concurrency_restricted: 'no',

		object_combine: function( obj, src ) {

			for ( var key in src ) {
				if ( src.hasOwnProperty( key ) ) obj[ key ] = src[ key ];
			}
			return obj;

		},

		check_subscriber_data: function(subscriber_data) {
			this.logged_in = 'yes';

			if ( window.pmc.subscription_v2.has_entitlements() ) {
				this.translate_subscriber_entitlements( subscriber_data.user.entitlements );
			}

			if ( subscriber_data.session !== null && subscriber_data.session.hasOwnProperty( 'concurrency_restricted' ) ) {
				if ( true === subscriber_data.session.concurrency_restricted ) {
					this.concurrency_restricted = 'yes';
				}
			}

			if ( subscriber_data.user.hasOwnProperty( 'acct' ) ) {

				var user_data = {};
				var user_reg_data = {};

				if ( subscriber_data.user.acct.hasOwnProperty( 'type' ) && subscriber_data.user.acct.type !== null ) {
					user_reg_data.pmc_account_type = subscriber_data.user.acct.type;
				}

				// Setting user details
				if ( subscriber_data.user.acct.hasOwnProperty( 'contact_id' ) && subscriber_data.user.acct.contact_id !== null ) {

					user_data = {
						'id': subscriber_data.user.acct.contact_id,
						'type': 'pmc'
					};
				}

				// Setting user registration details.
				if( window.pmc.subscription_v2.is_registered_user() ) {

					var reg_data = window.pmc.subscription_v2.get_registered_user_data();

					user_reg_data.pmc_campaign = reg_data.cam;
					user_reg_data.pmc_reg_date = reg_data.date;
					user_reg_data.pmc_days_since_reg = cxpmc.days_ago( reg_data.date );
				}

				user_data = this.object_combine( user_data, user_reg_data );  // Combine all user data.
				cxpmc.custom_parameters = this.object_combine( cxpmc.custom_parameters, user_reg_data ); // set custom parameters.

				cX.callQueue.push( ['addExternalId', user_data] );
				cX.callQueue.push( ['setUserProfileParameters', user_data] ); // Setting user profile.
				cX.callQueue.push( ['sendEvent', 'user-login', user_data] );  // Set dmp event.
			}
		},

		check_uls_data: function() {
			if ( window.uls.session.is_valid() ) {
				this.logged_in = 'yes';

				var entitlement = window.uls.session.get( 'entitlement' );
				this.translate_subscriber_entitlements( entitlement );
			}
		},

		translate_subscriber_entitlements: function(subscriber_entitlements) {
			if ( 'undefined' !== typeof window.pmc_meta ) {
				if ( 'wwd' === window.pmc_meta['lob'] ) {
					this.get_wwd_entitlements(subscriber_entitlements);
				} else if ( 'variety' === window.pmc_meta['lob'] ) {
					this.get_variety_entitlements(subscriber_entitlements);
				} else if ( 'rollingstone' === window.pmc_meta['lob'] ) {
					if ( subscriber_entitlements.indexOf('RollingStone.COM') !== -1 ) {
						this.subscriber_type = 'rs-com';
					} else if ( subscriber_entitlements.indexOf('RollingStone.BUNDLE') !== -1 ) {
						this.subscriber_type = 'rs-com';
					} else if ( subscriber_entitlements.indexOf('RS.REG') !== -1 ) {
						this.subscriber_type = 'rs-reg';
					}
				} else if ( 'sportico' === window.pmc_meta['lob'] ) {
					if ( subscriber_entitlements.indexOf('Sportico.COM') !== -1 ) {
						this.subscriber_type = 'sportico-com';
					} else if ( subscriber_entitlements.indexOf('SPORTICO.REG') !== -1 ) {
						this.subscriber_type = 'sportico-reg';
					}
				}
			}
		},

		/**
		 * Extremely hacky function to handle the logic of converting the entitlements from soa (an array) to cxense (must be a string).
		 * This will be cleaned up at some point, potentially with re-working how data is sent to cxense.
		 * Logic as follows:
		 * WWD.ARCHIVE = archive
		 * WWD.ARCHIVE + WWD.DD = archive-dd
		 * WWD.ARCHIVE + WWD.COMBO = archive-dd
		 * WWD.ARCHIVE + WWD.COM = archive
		 * WWD.COM = annual
		 * WWD.COM + WWD.DD = annual-dd
		 * WWD.COMBO = annual-dd
		 * WWD.DD = digital-daily
		 * @param entitlements
		 * @returns {string}
		 */
		get_wwd_entitlements: function(entitlements) {
			if (entitlements.indexOf('WWD.ARCHIVE') !== -1) {
				this.subscriber_type = 'archive';
				if (entitlements.indexOf('WWD.DD') !== -1 || entitlements.indexOf('WWD.COMBO') !== -1) {
					this.subscriber_type += '-dd';
				}
			} else if (entitlements.indexOf('WWD.COM') !== -1 || entitlements.indexOf('WWD.COMBO') !== -1) {
				this.subscriber_type = 'annual';
				if (entitlements.indexOf('WWD.DD') !== -1 || entitlements.indexOf('WWD.COMBO') !== -1) {
					this.subscriber_type += '-dd';
				}
			} else if (entitlements.indexOf('WWD.DD') !== -1) {
				this.subscriber_type = 'digital-daily';
			} else if (entitlements.indexOf('WWD.REG') !== 1) {
				this.subscriber_type = 'wwd-reg';
			}
		},

		/**
		 * Function to get entitlement for variety.
		 *
		 * @param entitlements
		 * @return {string}
		 */
		get_variety_entitlements: function(entitlements) {
			if ( entitlements.indexOf('Variety.VarietyVIP') !== -1 ) {
				this.subscriber_type = 'variety-vip';

				//If user is logged in uls and also in auth0
				if( window.uls.session.is_valid()
					&& window.uls.session.get( 'entitlement' ).indexOf('vy-digital') !== -1 ) {

					this.subscriber_type = 'variety-combo';

				}
			} else if ( entitlements.indexOf('vy-digital') !== -1 ) {
				this.subscriber_type = 'variety-printplus';
			} else if ( entitlements.indexOf('VY.REG') !== -1 ) {
				this.subscriber_type = 'vy-reg';
			}
		}
	}
};

cX.callQueue = cX.callQueue || [];
cX.CCE = cX.CCE || {};
cX.CCE.callQueue = cX.CCE.callQueue || [];

/**
 * Kicks everything off by loading cX.CCE script and setting callbacks to be run once Cxense JS is loaded.
 */
function initialize_cxense( cxpmc ) {
	cxpmc.load_cce( function() {
		if (!cxpmc.initialized) {
			cxpmc.initialize();
		}
	} );
}

/**
 * Checks if needed data exists
 *
 * @returns {boolean}
 */
function cxense_dependencies_exist() {
	return ( 'undefined' !== typeof window.pmc && 'undefined' !== typeof window.pmc.subscription_v2 );
}

/**
 * Make sure needed pmc object is available before running anything further.
 * The pmc object includes functions to check if page is proxied and reverse proxy the url if needed.
 * Since there is no hook available to use to ensure the pmc object is loaded,
 * if it isn't there at the time of this script running,
 * this will keep checking and initialize everything once it's loaded.
 */
if ( cxense_dependencies_exist() ) {
	initialize_cxense( cxpmc );
} else {
	cxense_check_dependencies = window.setInterval( function() {
		if ( cxense_dependencies_exist() ) {
			window.clearInterval( cxense_check_dependencies );
			initialize_cxense( cxpmc );
		}
	}, 500 );
}

// Export element only for jest test cases.
if (typeof exports !== 'undefined') {
	module.exports = cxpmc;
}

/**
 * Below are global functions to be used across all brands.
 * These JS functions are used in our Paywall modules in CCE.
 */

/**
 * Test browser support for third party cookies in an iframe.
 * Updates the global cookie3PSupported variable.
 * @returns void
 */
function testCookieSupport() {
	if (cookie3PSupported === null) {
		var frame = document.createElement('iframe');
		frame.id = '3pc';
		frame.src = 'https://subscriptions-static.pmc.com/cookies/read-cookie.html';
		frame.style.display = 'none';
		frame.style.position = 'fixed';
		frame.setAttribute('sandbox', 'allow-same-origin allow-scripts');
		document.body.appendChild(frame);

		window.addEventListener('message', function (event) {
			// Bail if this postMessage originated elsewhere
			// origin should be https://subscriptions-static.pmc.com
			if ("https://subscriptions-static.pmc.com" !== event.origin) {
				return;
			}
			if (event.data === '3pc.supported' || event.data === '3pc.unsupported') {
				cookie3PSupported = event.data === '3pc.supported';
			}
		}, false);
	}
}

/**
 * Send event to Google Analytics
 * @param eventCategory
 * @param eventAction
 * @param eventLabel
 * @returns void
 */
function sendGAEvent(eventCategory, eventAction, eventLabel) {
	if (window.ga) {
		ga('send', 'event', eventCategory, eventAction, eventLabel);
	}
}

/**
 * Set login redirect url
 * @param loginIdentifier className or id to query
 * @param defaultLoginUrl
 * @returns {string}
 */
function loginRedirectUrl(loginIdentifier, defaultLoginUrl) {
	var loginLink = document.querySelector(loginIdentifier);

	if (loginLink && loginLink.href) {
		var loginUrl = loginLink.href;

		// Get the encoded returnTo url
		// https://regex101.com/r/Wb3Nxs/1
		var rx = new RegExp("[?&]returnTo=([^&]+).*$");
		var returnVal = loginUrl.match(rx);

		if (returnVal) {
			var returnTo = decodeURIComponent(returnVal[1]);

			// Append a utm_source describing cxense
			if (returnTo.indexOf('?') === -1) {
				returnTo += '?utm_source=cxense';
			} else {
				returnTo += '&utm_source=cxense';
			}

			// encode the new returnTo url and replace value of returnTo in original login url
			returnTo = encodeURIComponent(returnTo);
			loginUrl = loginUrl.replace(returnVal[1], returnTo);
		}

		return loginUrl;
	}

	return defaultLoginUrl;
}
