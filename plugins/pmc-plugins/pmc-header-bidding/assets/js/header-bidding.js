/*
	PMC's Header Bidding with Prebid.js
	
	Minify this file with:
	sudo npm install -g uglify
	uglify -s header-bidding.js -o header-bidding.min.js
 */

/* global jQuery, pmc_header_bidder_script_object, pmc_meta */

"use strict";

var pbjs = pbjs || {};
pbjs.que = pbjs.que || [];

jQuery(document).ready(function () {

	if ("undefined" !== typeof pmc_adm_gpt && "undefined" !== typeof pmc_adm_gpt.settings.ad_list && pmc_adm_gpt.settings.ad_list.hasOwnProperty('default') ) {
		var ads = pmc_adm_gpt.settings.ad_list.default;
		if( pmc_adm_gpt.settings.ad_list.hasOwnProperty( 'time_gap_ads')
			&& "undefined" !== typeof pmc_adm_has_time_gap_ads
			&& true === pmc_adm_has_time_gap_ads ) {

			jQuery.merge( ads, pmc_adm_gpt.settings.ad_list.time_gap_ads );
		}
		var adUnits = [];

		if ( 'undefined' !== typeof pmc_meta && 'undefined' !== pmc_meta['page-type'] && 'gallery' === pmc_meta['page-type'] ) {

			if ( 'undefined' !== typeof pmc_header_bidder_script_object
				&& 'undefined' !== typeof pmc_header_bidder_script_object.pmc_hb_gallery_timeout ) {

				pmc_header_bidder_script_object.pmc_header_bidder_timeout = pmc_header_bidder_script_object.pmc_hb_gallery_timeout;

			}
		}
		pbjs.setConfig({
			bidderTimeout: pmc_header_bidder_script_object.pmc_header_bidder_timeout,
			consentManagement: {
				cmpApi: 'iab',
				timeout: 20000,
				allowAuctionWithoutConsent: false
			}
		});

		pbjs.pmc_request_bids =  function( consent_data ) {
			if ( 'object' === typeof consent_data && 'undefined' !== typeof consent_data.returnValue ) {
				pbjs.requestBids({
					bidsBackHandler: function() {
						pbjs.sendAdserverRequest();
					}
				});
			}
		};

		pbjs.que.push(function () {
			try {
				// Gets a list of all default ads on the page
				// Loop through each ad and setup the bidder params for each of them
				for (var i in ads) {
					if (ads[i].hasOwnProperty('bidders') && false !== ads[i]["bidders"]) {
						adUnits.push(ads[i]["bidders"]);
					}
				}

				pbjs.addAdUnits(adUnits);
				// Set a variable that we can access later so that we're only refreshing bids with configurations/responses
				window.pbjsAdUnits = adUnits;

			} catch(e){}

			// For EU user wait for GDPR check and then fetch bids.
			if ( 'object' === typeof pmc_meta &&
				'undefined' !== typeof pmc_meta.is_eu &&
				true === pmc_meta.is_eu
			) {

				pmc.hooks.add_action( 'pmc_adm_consent_data_ready', pbjs.pmc_request_bids);

			} else {

				pbjs.requestBids({
					bidsBackHandler: function() {
						pbjs.sendAdserverRequest();
					}
				});

				setTimeout( function() {
					pbjs.sendAdserverRequest();
				}, parseInt( pmc_header_bidder_script_object.pmc_header_bidder_timeout ) );
			}

		});

		pbjs.bidderSettings = {
			rubiconLite: {
				bidCpmAdjustment : function(bidCpm, bid){
					// adjust the bid in real time before the auction takes place
					return parseFloat( bidCpm ) * .50;
				}
			}
		};

		pbjs.sendAdserverRequest = function( slots_to_refresh ) {
			if( pbjs.adserverRequestSent ) return;
				pbjs.adserverRequestSent = true;
				googletag.cmd.push(function() {
				pbjs.que.push(function() {
					pbjs.setTargetingForGPTAsync();
					
					//Don't want to refresh when interrupt-ads are set. interrupt ad code will refresh
					if( ! document.getElementsByTagName("body")[0].className.match(/interrupt-ads/) ) {
					
						if ( typeof slots_to_refresh === 'undefined' ) {
							if ( 'undefined' !== typeof pmc_adm_gpt ) {
								pmc_adm_gpt.render_ads();
							} else {
								googletag.pubads().refresh();
							}
						}else{
							googletag.pubads().refresh( slots_to_refresh );
						}
					}
				});
			});
		};

	}

});