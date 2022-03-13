/**
 * JS for sticky ads on mobile devices
 *
 * To Generate a Minified Version:
 * sudo npm install uglify-js -g
 * cd pmc-sticky-ads/assets/js/
 * uglifyjs mobile.js -o mobile.min.js
 *
 * @author Amit Gupta
 * @since 2016-11-08
 * @version 2018-06-22 - Dhaval Parekh - READS-1221 - Added support for boomerang ads
 */
/* global jQuery, blogherads, pmc_sticky_ads_mobile_config, pmc_adm_gpt */
(function( $ ){

	function PMC_Sticky_Ads_Mobile() {

		this.all_ok = true;

		this.ad_displayed = false;
		this.ad_closed = false;

		if ( typeof pmc === 'undefined' ) {
			this.all_ok = false;
		}

		if ( typeof pmc_sticky_ads_mobile_config === 'undefined' ) {
			this.all_ok = false;
		}

		this.config = pmc_sticky_ads_mobile_config;

	}

	PMC_Sticky_Ads_Mobile.prototype.init = function() {

		if ( ! this.all_ok ) {
			return;
		}

		var self = this;

		$( '.btn-close-ad' ).on( 'click', function() {
			self.close_ad();
		} );

		if ( '1' === this.config.onload ) {
			this.maybe_show_ad();
		} else {
			this.delay_ad_display();
		}

	};

	PMC_Sticky_Ads_Mobile.prototype.maybe_show_ad = function() {

		if ( ! this.all_ok ) {
			return;
		}

		if ( this.ad_closed ) {
			return;
		}

		$( '.mobile-bottom-sticky-ad' ).removeClass( 'hidden' );

		this.ad_displayed = true;

	};

	PMC_Sticky_Ads_Mobile.prototype.close_ad = function() {

		var ad_div = document.querySelector( '.mobile-bottom-sticky-ad .ad-rotatable' );

		if ( ! this.all_ok ) {
			return;
		}

		$( '.mobile-bottom-sticky-ad' ).addClass( 'hidden' );

		this.ad_closed = true;

		if ( ad_div &&
			ad_div.id &&
			pmc_adm_gpt &&
			pmc_adm_gpt.auto_ad_refresh_timers &&
			ad_div.id in pmc_adm_gpt.auto_ad_refresh_timers ) {

			$( '.mobile-bottom-sticky-ad' ).remove(); // Removing sticky  ad markup from DOM
			pmc_adm_gpt.auto_ad_refresh_timers[ ad_div.id ] = {}; // Updating ad refresh object
		}


	};

	PMC_Sticky_Ads_Mobile.prototype.delay_ad_display = function() {

		if ( ! this.all_ok ) {
			return;
		}

		if ( this.ad_displayed || this.ad_closed ) {
			return;
		}

		if ( typeof this.config.leaderboard === 'undefined' || pmc.is_empty( this.config.leaderboard ) ) {
			return;
		}

		var self = this;

		$( '.' + this.config.leaderboard ).waypoint( {
			handler:  function( e, direction ) {
				// Support both v1.x and v2.x of waypoints
				// Because most PMC sites use 1.x, but BGR uses 2.x
				// In 1.x 'direction' contains the string direction
				// In 2.x 'e' contains the string direction
				if ( 'down' === e || 'down' === direction ) {
					self.maybe_show_ad();
				}
			},
			offset: 0
		} );

	};

	$( function() {
		var path,
			is_adhesion = false,
			pmc_sticky_ads_mob;

		if ( 'undefined' !== typeof googletag ) {
			googletag.cmd.push( function() {

				googletag.pubads().addEventListener( 'slotRenderEnded', function( event ) {

					if (
						'undefined' !== typeof event &&
						'object' === typeof event.slot &&
						'function' === typeof event.slot.getAdUnitPath
					) {

						path               = event.slot.getAdUnitPath();
						is_adhesion        = /adhesion/.test( path );
						pmc_sticky_ads_mob = new PMC_Sticky_Ads_Mobile();

						if ( is_adhesion ) {
							pmc_sticky_ads_mob.init();
						}
					}

				});
			});
		}

	});

	$( document ).ready( function() {

		var boomerang_ad_slots = [],
			pmc_sticky_ads_mob = false,
			ad_dom_element = false,
			index;

		if ( 'object' === typeof blogherads && 'function' === typeof blogherads.getSlots ) {

			boomerang_ad_slots = blogherads.getSlots();

			for ( index in boomerang_ad_slots ) {

				ad_dom_element = false;
				ad_dom_element = $( '#' + boomerang_ad_slots[ index ].domId );

				if ( 1 === ad_dom_element.data( 'is-adhesion-ad' ) ) {
					pmc_sticky_ads_mob = new PMC_Sticky_Ads_Mobile();
					pmc_sticky_ads_mob.init();
				}

			}
		}
	});

})( jQuery );


//EOF
