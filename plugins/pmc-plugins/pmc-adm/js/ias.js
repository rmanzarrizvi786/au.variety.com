/**
 * To Generate a Minified Version:
 * npm install uglify -g
 * cd pmc-adm/js/
 * uglify -s ias.js -o ias.min.js
 */

/* eslint linebreak-style: 0 */
/* global googletag, pmc_adm_gpt, __iasPET */

var pmc_adm_ias = {

	timeout: 2000, // milliseconds.
	ias_timeout_request: null,
	gpt_ad_slots: null,

	/**
	 * Set up IAS pet.js
	 */
	init: function() {

		window.__iasPET       = window.__iasPET || {};
		window.__iasPET.queue = window.__iasPET.queue || [];
		window.__iasPET.pubId =  '930203';

		window.googletag = window.googletag || {};
		window.googletag.cmd = window.googletag.cmd || [];
		this.initiate_ias_data();

	},

	/**
	 * Initialize ias data for initial request on page load.
	 */
	initiate_ias_data: function() {

		// make the PET request
		googletag.cmd.push( function() {

			// read the currently defined GPT ad slots for sending to the PET endpoint
			// defined all GPT ad slots before calling PET
			var ias_pet_slots = [],
				pmc_adlist;

			if ( 'settings' in pmc_adm_gpt && 'ad_list' in pmc_adm_gpt.settings && 'default' in pmc_adm_gpt.settings.ad_list ) {
				pmc_adlist = pmc_adm_gpt.settings.ad_list.default;

				pmc_adlist.forEach( function( adunit ) {

					if ( 'undefined' !== typeof adunit.is_lazy_load || 'no' === adunit.is_lazy_load ) {

						if ( 'undefined' !== typeof adunit.id && 'undefined' !== typeof adunit.width ) {

							ias_pet_slots.push({
								adSlotId: adunit.id,
								size: adunit.width,
								adUnitPath: adunit.slot
							});
						}
					}
				});

				pmc_adm_ias.ias_timeout_request = setTimeout( this.ias_data_handler, pmc_adm_ias.timeout );
				pmc_adm_ias.send_request( ias_pet_slots );
			}

		});
	},

	/**
	 * Handling IAS response.
	 */
	ias_data_handler: function() {

		clearTimeout( pmc_adm_ias.ias_timeout_request );
		__iasPET.setTargetingForGPT();

		//check for Amazon Apstag
		if ( 'object' === typeof apstag ) {
			return;
		}

		if ( null !==  pmc_adm_ias.gpt_ad_slots ) {
			googletag.pubads().refresh( pmc_adm_ias.gpt_ad_slots );
			pmc_adm_ias.gpt_ad_slots = null;
		} else {
			pmc_adm_gpt.render_ads();
		}

	},

	/**
	 * Send request to IAS.
	 */
	send_request: function( ias_pet_slots ) {

		if ( 'undefined' !== typeof ias_pet_slots ) {

			// make the request to PET.
			__iasPET.queue.push({
				adSlots: ias_pet_slots,
				dataHandler: pmc_adm_ias.ias_data_handler
			});
		}
	}

};

pmc_adm_ias.init();
