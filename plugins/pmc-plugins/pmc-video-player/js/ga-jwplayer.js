/**
 * To Generate a Minified Version:
 * npm install uglify -g
 * cd pmc-video-player/js/
 * uglify -s ga-jwplayer.js -o ga-jwplayer.min.js
 *
 * @TODO: Delete file theme code cleanup
 * ROP-2240 - 2021-02-02: Disable all jw ga tracking. Jwplayer dashboard has all the data
 *
 */
/*global jQuery*/
/*eslint no-undef: "error"*/
jQuery( document ).ready( function( $ ) {
	var pmc_ga_jwplayer = {

		ga_video_handler: {},
		target_selector: '[id ^=jwplayer_][id $=_div]',
		player_prefix: 'jw_',
		event_list: {},
		time_interval_events: {},
		time_percentage_events: {},

		player: {},

		/**
		 * Initialising JWPlayer ga event
		 *
		 * @returns {void}
		 */
		init: function() {
			// ROP-2240 - 2021-02-02: Disable all tracking
		},

		/**
		 * Setting up video ga tracking events
		 *
		 * @returns {void}
		 */
		setup_tracking: function() {
			// ROP-2240 - 2021-02-02: Disable all tracking
		},

		/**
		 * To setup JWPlayer event tracking on individual object.
		 *
		 * @param  obj HTML object of JWPlayer.
		 *
		 * @return void
		 */
		setup_tracking_by_object: function (obj) {
			// ROP-2240 - 2021-02-02: Disable all tracking
		},

		/**
		 * To get current Player Video Name.
		 *
		 * @param {object} _this player object.
		 *
		 * @returns {string/boolean}
		 */
		get_player_video_name: function( _this ) {

			var video_name = _this.getConfig().playlistItem.title;
			video_name = video_name.replace( /[\W_]+/g, '-' );

			if ( video_name ) {
				return video_name.toLowerCase();
			}

			return false;

		},

		/**
		 * To get concatenated label.
		 *
		 * @param {object} _this player object.
		 *
		 * @returns {string/boolean}
		 */
		get_concatenated_label: function( _this ) {

			var video_name = this.get_player_video_name( _this );
			var video_id   = _this.getConfig().playlistItem.mediaid;

			if ( ! ( video_name && video_id ) ) {
				return false;
			}

			return video_id + '_' + video_name;

		},

		/**
		 * To send GA events.
		 *
		 * @param {string}  event_action event action name.
		 * @param {string}  event_label event lable.
		 * @param {boolean} non_interaction is the event non interaction.
		 */
		send_ga_event: function( event_action, event_label, non_interaction ) {
			// ROP-2240 - 2021-02-02: Disable all tracking
		}
	};

	// Setting as global so that all other js code can use it's functions.
	window.pmc_ga_jwplayer = pmc_ga_jwplayer;
});
