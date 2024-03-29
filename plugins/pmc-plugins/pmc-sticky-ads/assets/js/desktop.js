/**
 * JS for sticky ads on desktop devices
 *
 * To Generate a Minified Version:
 * sudo npm install uglify-js -g
 * cd pmc-sticky-ads/assets/js/
 * uglifyjs desktop.js -o desktop.min.js
 *
 * @author Brian Van <Brian.VanNieuwenhoven@ey.com>
 * @since 2017-06-13
 * @version 2018-06-22 - Dhaval Parekh - READS-1221 - Added support for boomerang ads
 */
/* global jQuery, blogherads, pmc_sticky_ads_desktop_config */

(function( $ ){

	function PMC_Sticky_Ads_Desktop() {

		this.all_ok = true;

		this.ad_displayed = false;
		this.ad_closed = false;

		if ( typeof pmc === 'undefined' ) {
			this.all_ok = false;
		}

		if ( typeof pmc_sticky_ads_desktop_config === 'undefined' ) {
			this.all_ok = false;
		}

		this.config = pmc_sticky_ads_desktop_config;

	}

	PMC_Sticky_Ads_Desktop.prototype.init = function() {

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

	PMC_Sticky_Ads_Desktop.prototype.maybe_show_ad = function() {

		if ( ! this.all_ok ) {
			return;
		}

		if ( this.ad_closed ) {
			return;
		}

		$( '.desktop-bottom-sticky-ad' ).removeClass( 'hidden' );

		this.ad_displayed = true;

	};

	PMC_Sticky_Ads_Desktop.prototype.close_ad = function() {

		if ( ! this.all_ok ) {
			return;
		}

		$( '.desktop-bottom-sticky-ad' ).addClass( 'hidden' );

		this.ad_closed = true;

	};

	PMC_Sticky_Ads_Desktop.prototype.delay_ad_display = function() {

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
			pmc_sticky_ads_dsk;

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
						pmc_sticky_ads_dsk = new PMC_Sticky_Ads_Desktop();

						if ( is_adhesion ) {
							pmc_sticky_ads_dsk.init();
						}
					}

				});
			});
		}

	});

	$( document ).ready( function() {

		var boomerang_ad_slots = [],
			pmc_sticky_ads_dsk = false,
			ad_dom_element = false,
			index;

		if ( 'object' === typeof blogherads && 'function' === typeof blogherads.getSlots ) {

			boomerang_ad_slots = blogherads.getSlots();

			for ( index in boomerang_ad_slots ) {

				ad_dom_element = false;
				ad_dom_element = $( '#' + boomerang_ad_slots[ index ].domId );

				if ( 1 === ad_dom_element.data( 'is-adhesion-ad' ) ) {
					pmc_sticky_ads_dsk = new PMC_Sticky_Ads_Desktop();
					pmc_sticky_ads_dsk.init();
				}

			}
		}
	});

})( jQuery );


//EOF
