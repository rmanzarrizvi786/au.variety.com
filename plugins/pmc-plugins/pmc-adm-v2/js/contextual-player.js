/**
 * Contextual player related js code
 * npm install uglify -g
 * cd pmc-adm/js/
 * uglify -s contextual-player.js -o contextual-player.min.js
 */

/* eslint linebreak-style: 0 */
/* global jQuery, contextual_player */

var pmc_contextual_player = {

	player_main_div: '.pmc-contextual-player',
	message_pattern: 'pmcadm:dfp:isdirect=true',

	/**
	 * Set up Contextual player
	 */
	init: function() {

		this.bind_events();

	},

	/**
	 *  Binding to message event to listen if the page is serving direct sold ads or not
	 */
	bind_events: function() {

		var self = this;

		self.pause_triggered = false;

		jQuery( window ).on( 'message', function( wrappedEvent ) {
			var event = wrappedEvent.originalEvent;

			if ( 'string' === typeof event.data ) {

				/*
				 Only process the message event, if it is pmcadm:dfp:isdirect=true
				 */
				if ( event.data.substring( 0, self.message_pattern.length ) === self.message_pattern ) {
					if (
						'object' !== typeof pmc_adm_config
						|| 'string' !== typeof pmc_adm_config.contextual_on_direct_sold
						|| 'show' !== pmc_adm_config.contextual_on_direct_sold
					) {
						//Direct sold ads are running and we need to kill contextual player on the page
						self.remove_contextual_player();
					}
				}
				else if ( event.data === 'pmc_show_interrupt_ads' ) {
					if ( window.contextual_player ) {
						try {
							// We need to determine if we can pause the player
							if ( window.contextual_player.getState() !== 'playing' ) {
								var jwconfig = window.contextual_player.getConfig();
								if ( jwconfig.autostart === 'viewable' && jwconfig.viewable ) {
									self.pause_triggered = true;
								}
							} else {
								self.pause_triggered = true;
							}

							if ( self.pause_triggered ) {
								window.contextual_player.pause();
							}
						}
						catch ( e ) {
							// do nothing
						}
					}
				}
				else if ( event.data === 'pmc_hide_interrupt_ads' ) {
					if ( self.pause_triggered ) {
						window.contextual_player.play();
						self.pause_triggered = false;
					}
				}
			}
		});

		if ( 'object' === typeof contextual_player ) {
			contextual_player.on( 'play', self.current_playing );
			contextual_player.on( 'adPlay', self.current_playing );

			// For JW 8.9.0+
			contextual_player.on( 'relatedReady', function () {

				var relatedPlugin = contextual_player.getPlugin( 'related' );

				if ( 'object' === typeof relatedPlugin ) {

					relatedPlugin.on( 'feedShown', function () {

						self.create_playlist_nav();
						jQuery( '.pmc-contextual-player .jw-related-control' ).on( 'click', self.current_playing );

					});
				}
			});

			// For ≤ JW 8.8.6
			contextual_player.on( 'ready', function () {
				var relatedPlugin = contextual_player.getPlugin( 'related' );

				if ( 'object' === typeof relatedPlugin ) {
					relatedPlugin.on( 'feedShown', function () {

						self.create_playlist_nav();
						jQuery( '.pmc-contextual-player .jw-related-control' ).on( 'click', self.current_playing );

					});
				}
			});
		}
	},

	/**
	 * Remove Contextual player from the dom
	 */
	remove_contextual_player: function() {

		var player = jQuery( this.player_main_div );

		if ( 0 < player.length ) {

			player.remove();

		}
	},

	/**
	 * Set current playing item in carousel
	 */
	current_playing: function () {
		var playlist_index, current_item, all_item, now_playing;

		playlist_index = contextual_player.getPlaylistIndex();
		all_item       = jQuery( '.pmc-contextual-player .jw-related-shelf-item.is-active' ).removeClass( 'is-active' );
		current_item   = jQuery( '.pmc-contextual-player .jw-related-shelf-item[data-jw-index=' + playlist_index + ']' );
		now_playing    = jQuery( '<div class="contextual-self-now-playing">VIEWING</div>' );

		if ( 'object' === typeof current_item && 1 === current_item.length ) {
			current_item.addClass( 'is-active' );
		}

		if ( 0 === jQuery( '.jw-related-shelf-item[data-jw-index=' + playlist_index + '] .contextual-self-now-playing' ).length ) {
			current_item.find( '.jw-related-shelf-item-image' ).append( now_playing );
		}

	},

	/**
	 * Creates navigation button(arrows) for contextual carousel
	 */
	create_playlist_nav: function () {

		if ( 0 === jQuery( '.pmc-contextual-player .l-adm-contextual-video__shadow' ).length ) {

			var shadow_left = jQuery( '<div class="l-adm-contextual-video__shadow l-adm-contextual-video__shadow-left"></div>' ),
				shadow_right = jQuery( '<div class="l-adm-contextual-video__shadow l-adm-contextual-video__shadow-right"></div>' );

			jQuery( '.pmc-contextual-player .jw-related-control-left' ).prepend( shadow_left );
			jQuery( '.pmc-contextual-player .jw-related-control-right' ).prepend( shadow_right );

		}

	}

};

pmc_contextual_player.init();
