<?php

/**
 * Cxense script to render on page
 * This script sends data to Cxense for page views and modules that need to be loaded.
 * Page views need correct site id, custom parameters and in some cases (gallery and/or proxy pages), corrected URL
 * Modules need the div id to replace content with, the module id and  and in some cases (gallery and/or proxy pages), corrected URL
 *
 * @package pmc-cxense
 *
 */

// @codeCoverageIgnoreStart - This script will be converted to a js file and tested in Jest
?>
<!-- Begin Cxense scripts -->

<script type="text/javascript">
	var cX = window.cX = window.cX || {},
		cxpmc,
		check_dependencies;

	cxpmc = {

		custom_parameters: <?php echo wp_json_encode( $custom_parameters ); ?>,

		initialized: false,

		is_proxied: false,

		modules: <?php echo wp_json_encode( $modules ); ?>,

		page_location: <?php echo wp_json_encode( $page_location ); ?>,

		paywall_module: <?php echo wp_json_encode( $paywall_module ); ?>,

		site_id: <?php echo wp_json_encode( $site_id ); ?>,

		/**
		 * Helper function for calling cX.CCE.run.
		 * If page is proxied, adds a parameter for sending the page with the correct domain.
		 * @param module
		 * @returns {[string, *]|[string, *, {context: {url: undefined}}]}
		 */
		get_module_run_parameters: function( module ) {
			if ( this.is_proxied ) {
				return [ 'run', module, { 'context': { 'url' : this.page_location } }];
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
				return [ 'sendPageViewEvent', { 'location':this.page_location } ];
			}
			return [ 'sendPageViewEvent' ];
		},

		/**
		 * Initializes needed variables if the page is proxied.
		 * @param pmc
		 */
		initialize: function( pmc ) {
			if ( ! this.initialized ) {
				if ( pmc.is_proxied() ) {
					this.is_proxied = true;
					this.page_location = this.get_page_location();
				}

				this.initialized = true;
			}
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
			this.modules.forEach( function( module ) {
				if ( document.getElementById( module.targetElementId ) ) {
					cX.CCE.callQueue.push( cxpmc.get_module_run_parameters( module ) );
				}
			} );
		},

		/**
		 * Loads the paywall module if the div is present on the page.
		 * The callback checks to make sure the needed variables are not empty.
		 */
		load_paywall: function() {
			if ( document.getElementById( 'cx-paywall' ) ) {
				cX.CCE.callQueue.push( cxpmc.get_module_run_parameters( this.paywall_module ) );
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
				cX.callQueue.push( [ 'setSiteId', this.site_id ] );

				// Set custom parameters if there are any defined
				if ( Object.keys( this.custom_parameters ).length ) {
					cX.callQueue.push( [ 'setCustomParameters', JSON.parse( JSON.stringify( this.custom_parameters ) ) ] );
				}

				cX.callQueue.push( this.get_page_view_parameters() );
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
			cxpmc.initialize( window.pmc );
			cxpmc.run();

			if ( cxpmc.modules.length ) {
				cxpmc.load_modules();
			}

			if ( cxpmc.paywall_module.hasOwnProperty( 'widgetId') ) {
				cxpmc.load_paywall();
			}
		} );
	}

	/**
	 * Checks if needed data exists
	 *
	 * @returns {boolean}
	 */
	function dependencies_exist() {
		return ( 'undefined' !== typeof window.pmc );
	}

	/**
	 * Loads initial cX script. The cX.CCE.js file will also load cX.js if this fails.
	 */
	( function() {
		var script = document.createElement('script');

		script.type  = 'text/javascript';
		script.async = 'async';
		script.src   = 'https://scdn.cxense.com/cx.js';
		document.getElementsByTagName( 'head' )[0].appendChild( script );
	} )();

	/**
	 * Make sure needed pmc object is available before running anything further.
	 * The pmc object includes functions to check if page is proxied and reverse proxy the url if needed.
	 * Since there is no hook available to use to ensure the pmc object is loaded,
	 * if it isn't there at the time of this script running,
	 * this will keep checking and initialize everything once it's loaded.
	 */
	if ( dependencies_exist() ) {
		initialize_cxense( cxpmc );
	} else {
		check_dependencies = window.setInterval( function() {
			if ( dependencies_exist() ) {
				window.clearInterval( check_dependencies );
				initialize_cxense( cxpmc );
			}
		}, 100 );
	}
</script>


