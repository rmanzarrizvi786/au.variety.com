/**
 * This script will add custom params ( keywords, pageview, referrer and skin value)
 * to the video VAST tag
 *
 * To Generate a Minified Version:
 * npm install uglify -g
 * cd pmc-video-player/js/
 * uglify -s video-ads.js -o video-ads.min.js
 */
/* global pmc_video_player_ads, jwplayer */

jQuery( document ).ready( function( $ ) {

	var pmc_video_ads = {

		jwplayer_selector   : '[id ^=jwplayer_][id $=_div]',

		init: function() {

			var jwplayers_divs = $( this.jwplayer_selector ),
				_self = this;

			if ( 0 <= jwplayers_divs.length && 'function' === typeof pmc_jwplayer ) {

				//There could be more than one jwplayer on page. Events needs to be added to all players
				$.each ( jwplayers_divs, function( key, obj ) {
					if ( obj.id ) {
						_self.setup_jwplayer( obj.id );
					}
				});
			}
		},

		// Note: Do not remove this function as it is reference by many places
		setup_jwplayer: function (object_id) {
			pmc_jwplayer(object_id).apply_caption().monetize();
		}

	};

	pmc_video_ads.init();

	//Setting as global so that all other video header bidding code can access default video custom params
	window.pmc_video_ads = pmc_video_ads;

});
