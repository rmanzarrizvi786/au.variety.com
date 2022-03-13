/**
 * Minify this file with:
 * sudo npm install -g uglify
 *
 * cd ./pmc-floating-video/assets/js/
 * uglifyjs floating-jwplayer.js -o floating-jwplayer.min.js
 */

var pmc_floating_player = {};

(function( $ ) {

	pmc_floating_player = {

		player_instance: null,
		player_selector: '[id ^=jwplayer_][id $=_div]',

		/**
		 * Init function.
		 */
		init: function() {

			var $player_obj = $( this.player_selector ).first();

			if ( 0 >= $player_obj.length ) {
				return false;
			}

			this.player_instance = jwplayer( $player_obj.attr( 'id' ) );

			this.player_instance.on('ready', function (data) {
				if ('object' !== typeof pmcFloatingVideoOptions) {
					return;
				}
				// Corresponds to array in setup
				var floatingCssClasses = [
					'jw-floating-top-full-width',
					'jw-floating-bottom-right',
					'jw-floating-bottom-stripe',
					'jw-floating-top-left',
					'jw-floating-top-right',
				];
				// Gets the current setup
				var currentFloatingPostion = parseInt(
					pmcFloatingVideoOptions.jwplayer_floating_placement,
					10
				);
				// Finds the Player and sets it's css class accordingly
				var player_container = jwplayer().getContainer();
				if (
					null !== currentFloatingPostion &&
					floatingCssClasses[currentFloatingPostion] &&
					null !== player_container
				) {
					$(player_container).addClass(
						floatingCssClasses[currentFloatingPostion]
					);
				}
			});

			this.player_instance.on( 'resize', function ( data ) {

				var ad_div = document.querySelector( '.mobile-bottom-sticky-ad' );

				if (
					'object' === typeof pmc_meta
					&& ( ( 'desktop' === pmc_meta.env && 230 > data.height ) || ( 'mobile' === pmc_meta.env && 90 === data.height ) )
					&& 'undefined' === typeof pmc_floating_player.floating_check
				) {

					var player_close_btn = document.querySelector( '.jw-controls .jw-svg-icon-close' );

					player_close_btn.style.display = 'none';

					if ( null !== ad_div ) {
						ad_div.remove(); // Removing mobile adhesion ad markup from DOM
					}

					if (
						null !== ad_div &&
						'object' === typeof pmc_adm_gpt &&
						pmc_adm_gpt.auto_ad_refresh_timers &&
						ad_div.id in pmc_adm_gpt.auto_ad_refresh_timers
					) {
						pmc_adm_gpt.auto_ad_refresh_timers[ad_div.id] = {}; // And make sure this ad is never refreshed
					}

					if ( 'object' === typeof blogherads && 'object' === typeof blogherads.getSlotById('skm-ad-bottom') ) {
						blogherads.destroySlots( ['skm-ad-bottom'] );
					}

					pmc_floating_player.floating_check = true;

					//Display close button after 5 seconds. This is to get preroll ad impression.
					setTimeout(function () {
						player_close_btn.style.display = 'block';
					}, 5000 );
				}
			} );

			if ( 'undefined' !== typeof pmc && 'undefined' !== typeof pmc.hooks && 'undefined' !== typeof pmc.hooks.add_action ) {
				pmc.hooks.add_action( 'pmc_adm_dfp_direct_sold', this.dismiss_floating_player );
			}

		},

		dismiss_floating_player: function () {

			var floating_container = document.querySelector( '.jw-flag-touch .jw-wrapper' );

			if( null !== floating_container ) {
				floating_container.classList.add( 'pmc-jw-disable-floating' );
				pmc_floating_player.player_instance.pause();
			}
		}

	};

	pmc_floating_player.init();

})( jQuery );
