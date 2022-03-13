/**
 * To Generate a Minified Version:
 * npm install uglify -g
 * cd pmc-amazon-apstag/assets/js/
 * uglify -s amazon-apstag.js -o amazon-apstag.min.js
 */
/* eslint linebreak-style: ["error", "windows"] */
/* global jQuery, pmc_meta, apstag */
var pmc_apstag = pmc_apstag || {};
jQuery( document ).ready( function () {
	if ( 'undefined' !== typeof apstag ) {
		var is_video = false;
		pmc_apstag.amzn_cust_params = '';

		if ( 'is_video' in pmc_apstag  && 'enabled' === pmc_apstag.is_video ) {
			is_video = true;
		}

		pmc_apstag.ad_slots = function() {
			var apstag_adlist = [],
				apstag_slots = [],
				slots = {};

			if ( 'settings' in pmc_adm_gpt && 'ad_list' in pmc_adm_gpt.settings && 'default' in pmc_adm_gpt.settings.ad_list ) {
				apstag_adlist = pmc_adm_gpt.settings.ad_list.default;
			}
			apstag_adlist.forEach( function( adunit ) {
				if ( ! ('oop' in adunit ) && 'targeting' in adunit && 'pos' in adunit.targeting ) {

					slots = {};
					if ( 'undefined' !== typeof adunit.id && 'undefined' !== typeof adunit.width ) {
						slots.slotID = adunit.id;
						slots.sizes = adunit.width;
						apstag_slots.push( slots );
					}

				}
			} );
			//Add a slot for video ad.
			if ( true === is_video ) {
				apstag_slots.push({
					slotID: 'videoSlot',
					mediaType: 'video'
				});
			}
			return apstag_slots;
		};

		/**
		 * Fetch bids from Amazon Apstag.
		 *
		 * @param {object} bid_config - bid configuration object
		 * Ex:
		 * {
		 * 		slots: [
		 * 			{
		 * 				slotID: 'div-gpt-ad-1475102693815-0',
		 * 				sizes: [[300, 250], [300, 600]]
		 * 			}
		 * 		]
		 * 		, timeout: 1000
		 * 		}.
		 * @param {object} slots_to_refresh - DFP Ad unit slots that need to be refreshed.
		 */
		pmc_apstag.fetch_bids = function( bid_config, slots_to_refresh ) {
			apstag.fetchBids( bid_config, function( bids ) {
				if ( is_video ) {
					handle_apstag_video_bid(bids);
				}

				//No need to refresh display ads if slots_to_refresh has only video slot
				if (
					'undefined' !== typeof slots_to_refresh &&
					1 === slots_to_refresh.length &&
					0 in slots_to_refresh &&
					'slotID' in slots_to_refresh[0] &&
					'videoSlot' === slots_to_refresh[0].slotID ) {

					return;
				}

				// Call back function after bids returned
				googletag.cmd.push( function () {
					apstag.setDisplayBids();
					//Don't call googletag.pubads().refresh() unless Prebidjs is disabled.
					//Usaully prebid wrapper takes more time than any individual partners.
					if ( 'undefined' === typeof pmc_header_bidder || 'undefined' === typeof pbjs || '1' !== pmc_header_bidder.active ) {
						if ( 'undefined' !== typeof pmc_adm_gpt && 'undefined' === typeof slots_to_refresh ) {
							pmc_adm_gpt.render_ads();
						} else {
							if ( typeof slots_to_refresh !== 'undefined' ) {
								googletag.pubads().refresh( slots_to_refresh );
							} else {
								googletag.pubads().refresh();
							}
						}
					}
				} );
			} );
		};

		/**
		 * Function to handle the video bid
		 *
		 * @param {object} videoBid - Video bids returned from A9
		 */
		handle_apstag_video_bid = function( bids ) {
			var videoBid = bids.filter( function( bid ){ return bid.mediaType === 'video' } )[0];
			if (videoBid) {
				// add the encoded query string params to the scp param on the vastTagURL
				pmc_apstag.amzn_cust_params = '&scp=' + videoBid.encodedQsParams;
			}

		};

		/**
		 * Function to build vast tag
		 *
		 */
		build_apstag_video_tag = function( jw_player_id ) {
			var vast_tag = '';
			var page_url = window.location.href;

			if ( 'undefined' !== typeof jwplayer
				&& 'defaults' in jwplayer
				&& 'advertising' in jwplayer.defaults
				&& 'schedule' in jwplayer.defaults.advertising
				&& 'undefined' !== typeof jwplayer.defaults.advertising.schedule[0] ) {

				vast_tag = jwplayer.defaults.advertising.schedule[0].tag;
			} else {
				return '';
			}

			//Append amazon custom params if they exist.
			if ( 'undefined' !== typeof amzn_cust_params && '' !== vast_tag ) {
				pmc_apstag.amzn_cust_params = amzn_cust_params;
				vast_tag += amzn_cust_params;
			}
			return vast_tag;
		};

		/**
		 * Re Fetch bids from Amazon Apstag by passing DFP adunit slots.
		 *
		 * @param {object} slots_to_refresh - DFP Ad unit slots that need to be refreshed.
		 */
		apstag_refresh_bids = function( slots_to_refresh ) {

			var apstag_slots = [],
				slot = {};

			if ( 0 < slots_to_refresh.length ) {

				//check if it is for video bid then skip fetching slot div id
				if ( 'string' === typeof slots_to_refresh[0].mediaType && 'video' === slots_to_refresh[0].mediaType ) {

					apstag_slots = slots_to_refresh;

				} else {

					slots_to_refresh.forEach(function (item) {

						slot.slotID = item.getSlotElementId();
						slot.sizes = [];

						item.getSizes().forEach(function (ad_size) {

							slot.sizes.push([ad_size.getWidth(), ad_size.getHeight()]);

						});

						apstag_slots.push(slot);
					});
				}

			} else {
				apstag_slots = pmc_apstag.ad_slots();
			}

			pmc_apstag.fetch_bids( {
				slots: apstag_slots,
				timeout: 1000
			}, slots_to_refresh );
		};

		// Wait or GDPR check and then fetch bids.
		if (
			( 'object' === typeof pmc_meta && 'undefined' !== typeof pmc_meta.is_eu && true === pmc_meta.is_eu ) ||
			( -1 !== window.location.search.indexOf( 'region=eu' ) )
		) {

			pmc.hooks.add_action( 'pmc_adm_consent_data_ready', function( consent_data) {
				if ( 'object' === typeof consent_data && 'undefined' !== typeof consent_data.returnValue ) {
					pmc_apstag.fetch_bids({
						slots: pmc_apstag.ad_slots()
					});
				}
			});

		} else {
			pmc_apstag.fetch_bids({
				slots: pmc_apstag.ad_slots()
			});
		}
	}

});
