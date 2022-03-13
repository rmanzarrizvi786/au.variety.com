/*
 * This script is written to handle PMC Video Playlist manager
 *
 * This scripts manage the video player added by PMC Video Playlist manager plugin on single posts.
 */

/* global jwplayer, pmc */

( function( window, $ ) {

	var PMCVideoPlayList = function() {
		var self = this,
			cPvmPlayer;

		/**
		 * Bind all events and prepare the class.
		 */
		self.initialize = function() {

			cPvmPlayer = $( '.c-pvm-player' );

			// Setup Player.
			cPvmPlayer.each( self.setupPlayer );

		};

		/**
		 * Set up the video player.
		 *
		 * @returns {void}
		 */
		self.setupPlayer = function() {
			var player = $( this );
			var iframe = $( 'iframe', player );
			var src = iframe.data( 'pvm-src' ) || '';
			var jwPlayers = $( '[id ^=jwplayer_][id $=_div]' );
			var currentPlayer;
			var currentPlayerDetail = {
				instance: false,
				id: false,
				type: false
			};

			// Handle the YouTube Player
			// Note: JW Player's markup can contain an iframe, so make sure this iframe is not one of those.
			if ( iframe.length && ! iframe.parents( '.jwplayer' ).length ) {

				currentPlayerDetail.type = 'youtube';

				if ( '' === src ) {
					src = iframe.attr( 'src' ) || '';
				}

				// Make sure player is static when there's no `src` on the iframe.
				if ( ! player.hasClass( 'is-static' ) && '' === iframe.attr( 'src' ) ) {
					player.addClass( 'is-static' );
				}

				if ( '' !== src ) {

					// Add `autoplay=1` to the `src` so that the user doesn't have to click twice.
					if ( -1 !== src.indexOf( 'autoplay' ) ) {
						src = src.replace( /autoplay=[01]/i, 'autoplay=1' );
					} else {
						src = src + '&autoplay=1';
					}

					// Activate the player.
					player.on( 'click', '.c-pvm-player__link', function( e ) {
						e.preventDefault();

						if ( ! player.hasClass( 'is-static' ) ) {
							return;
						}

						if ( ! iframe.attr( 'src' ) ) {
							iframe.attr( 'src', src );
						} else {
							iframe.trigger( 'yt-player:play' );
						}
						player.removeClass( 'is-static' );
					});

					// Deactivate the player.
					player.on( 'player:reset', function() {
						iframe.trigger( 'yt-player:stop' );
						player.addClass( 'is-static' );
					});
				}
			}

			//Handling jw player
			if ( 0 < jwPlayers.length ) {
				currentPlayerDetail.type = 'jwplayer';
				currentPlayer = '';
				currentPlayerDetail.id = false;
				currentPlayerDetail.instance = '';

				player.on( 'click', '.c-pvm-player__link', function( e ) {
					currentPlayer = player.find( '[id ^=jwplayer_][id $=_div]' ).first();
					currentPlayerDetail.id = currentPlayer.attr( 'id' );

					e.preventDefault();

					if ( 'function' !== typeof jwplayer || 'undefined' === typeof currentPlayerDetail.id || '' === currentPlayerDetail.id ) {
						return;
					}

					currentPlayerDetail.instance = jwplayer( currentPlayerDetail.id );

					if ( ! player.hasClass( 'is-static' ) ) {
						return;
					}

					if ( 'object' === typeof pmc && 'object' === typeof pmc.hooks && 'function' === typeof pmc.hooks.do_action ) {
						pmc.hooks.do_action( 'pvm-onclick-carousel-player-link', currentPlayerDetail.instance, currentPlayerDetail.id, currentPlayerDetail.type );
					}

					player.removeClass( 'is-static' );
					currentPlayerDetail.instance.play( true );
					window.playerInstance = currentPlayerDetail.instance;
				});

				player.on( 'player:reset', function() {
					if ( player.hasClass( 'is-static' ) ) {
						return;
					}

					player.addClass( 'is-static' );

					if ( 'undefined' !== typeof window.playerInstance ) {
						window.playerInstance.pause( true );
					}

				});
			}
		};

		self.initialize();
	};

	$( function() {
		window.PMCVideoPlayList = new PMCVideoPlayList();
	});

}( window, jQuery ) );
