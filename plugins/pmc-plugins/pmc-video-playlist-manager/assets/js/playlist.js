/*
 * This script is written to handle PMC Video Playlist manager
 *
 * This scripts manage the video player carousel activity (slide changes, playlist item changes, next/prev slide event).
 */

/* global jwplayer, pmc, pmc_video_ads, pmc_jwplayer */

( function( window, $ ) {

	var PMCVideoPlaylistItems = function() {

		var self = this;

		/**
		 * Active slide index.
		 */
		var activeSlideIndex = 99;

		/**
		 * Total number of slides.
		 */
		var slidesCount;

		/**
		 * Collection of slides (jQuery)
		 */
		var slides;

		/**
		 * Collection of slide triggers (jQuery)
		 */
		var triggers;

		/**
		 * Next slide trigger.
		 */
		var nextTrigger;

		/**
		 * Previous slide trigger.
		 */
		var prevTrigger;

		/**
		 * Maximum margin allow for playlist item.
		 */
		var maxMargin;

		/**
		 * Visible playlist item slide count.
		 */
		var visibleItemCount;

		/**
		 * PLaylist item Box width.
		 * (used for shifting active item).
		 */
		var listItemSize;

		/**
		 * Store the horizontal (client X) coordinates
		 */
		var xDown;

		/**
		 * JWPlayer Player container div ID.
		 * @type {string}
		 */
		var jwplayerInstanceDOMID = 'pvm_jwplayer_carousel_div';

		/**
		 * Initialize.
		 */
		self.initialize = function() {

			var el = $( '[data-pvm-video-carousel]' ),
				playlist = $( '.l-pvm-video--carousel .l-pvm-video__playlist li' ),
				activeSlide;

			/**
			 * Flag for if JWPlayer is activate or not.
			 * @type {bool}
			 */
			this.isJWPlayerActive = false;

			/**
			 * JWPlayer Instance.
			 * @type {object}
			 */
			this.jwplayerInstance = false;

			if ( 'function' === typeof jwplayer ) {
				this.jwplayerInstance = jwplayer( jwplayerInstanceDOMID );
			}

			/**
			 * Slider element.
			 *
			 * @type {*|HTMLElement}
			 */
			this.slider = el.find( '[data-pvm-video-slider]' );

			/**
			 * JWplayer slide element
			 *
			 * @type {*|HTMLElement}
			 */
			this.jwplayerSlide = $( '#pvm-carousel-jwplayer' );

			slides = el.find( '[data-pvm-video]' );
			if ( 0 === slides.length || 0 === playlist.length ) {
				return;
			}
			triggers = el.find( '[data-pvm-video-trigger]' );
			nextTrigger = triggers.filter( '[data-pvm-video-trigger="next"]' );
			prevTrigger = triggers.filter( '[data-pvm-video-trigger="prev"]' );
			slidesCount = slides.length;

			activeSlide = slides.filter( '.is-active' ).first();

			if ( 1 === activeSlide.length ) {
				self.setActiveSlide( activeSlide );
			} else {
				self.setActiveSlide( slides.first() );
			}

			$( document ).ready( function() {
				self.initializeJWplayerSetup();
			});

			if ( 'object' === typeof pmc && 'object' === typeof pmc.hooks && 'function' === typeof pmc.hooks.add_action ) {
				pmc.hooks.add_action( 'pvm-onclick-carousel-player-link', function() {
					self.onClickPlayerLink.apply( self, arguments );
				});
			}

			triggers.on( 'click', function( e ) {
				e.preventDefault();
				self.changeSlide( e.currentTarget.dataset.pvmVideoTrigger );
			});

			playlist.on( 'touchstart', self.swipeTouchStart );
			playlist.on( 'touchmove', self.swipeTouchMove );
		};

		/**
		 * To initialize JWPlayer for video carousal.
		 *
		 * @return void
		 */
		self.initializeJWplayerSetup = function() {

			var playerObject = this.slider.find( '[id ^=jwplayer_][id $=_div]' ).first(),
				playerElement = false,
				videoHash = false,
				playlist = false;

			// If playerObject don't have any length.
			// that mean in whole carousal we don't have any JWPlayer.
			if ( 'function' !== typeof jwplayer || 'undefined' === typeof playerObject || 0 >= playerObject.length ) {
				return;
			}

			playerElement = document.getElementById( jwplayerInstanceDOMID );
			videoHash = playerObject.data( 'videoid' );
			playlist  = playerObject.data( 'jsonfeed' );

			if ( null === playerElement ) {
				return;
			}

			if ( 'undefined' === typeof playlist || ! playlist ) {
				playlist = 'https://content.jwplatform.com/feeds/' + videoHash + '.json';
			}

			this.jwplayerInstance = pmc_jwplayer( jwplayerInstanceDOMID ).setup({
					playlist: playlist,
					ph: 2,
					autostart: false
				}).instance();

		};

		/**
		 * Determine the active slide.
		 */
		self.setActiveSlide = function( video ) {

			var videoSlug = video.data( 'pvm-video' ),
				index = slides.index( video ),
				margin;

			if ( index === activeSlideIndex ) {
				return;
			}

			// Shift the carousel and set active item.
			if ( isNaN( visibleItemCount ) ) {
				listItemSize = triggers[3].offsetWidth;
				visibleItemCount = ( video.width() / listItemSize ).toPrecision( 1 );
				maxMargin = ( slidesCount - visibleItemCount ) * listItemSize;
			}

			margin = ( index - ( visibleItemCount - 1.5 ) ) * listItemSize;

			if ( margin > maxMargin ) {
				margin = maxMargin;
			} else if ( 0 > margin ) {
				margin = 0;
			}

			$( 'ul li.l-pvm-video__item' ).eq( 0 ).css( 'margin-left', -margin + 'px' );
			slides.eq( 0 ).css( 'margin-left', -index * 100 + '%' );

			slides.filter( '.is-active' ).removeClass( 'is-active' ).find( '.c-pvm-player' ).trigger( 'player:reset' );
			slides.filter( '[data-pvm-video="' + videoSlug + '"]' ).addClass( 'is-active' );

			// Set active trigger (thumb).
			triggers.removeClass( 'is-active' ).filter( '[data-pvm-video-trigger=\'' + videoSlug + '\']' ).addClass( 'is-active' );

			// Toggle prev/next triggers.
			nextTrigger.toggleClass( 'is-hidden', index === slidesCount - 1 );
			$( 'div.l-pvm-video__shadow-right' ).toggleClass( 'is-hidden', index === slidesCount - 1 );
			prevTrigger.toggleClass( 'is-hidden', 0 === margin );
			$( 'div.l-pvm-video__shadow-left' ).toggleClass( 'is-hidden', 0 === margin );

			activeSlideIndex = index;
		};

		/**
		 * Change the active slide.
		 */
		self.changeSlide = function( videoSlug ) {

			var targetIndex = 0,
				video;

			self.removeJWplayerDOM();

			if ( 'next' === videoSlug ) {
				self.scrollPlaylist( videoSlug );
			} else if ( 'prev' === videoSlug ) {
				self.scrollPlaylist( videoSlug );
			} else {
				video = slides.filter( '[data-pvm-video="' + videoSlug + '"]' );

				targetIndex = slides.index( video );
				self.setActiveSlide( slides.eq( targetIndex ) );
				self.triggerPlay( video );
			}
		};

		/**
		 * Scrolls the video playlist items.
		 *
		 * @param trigger string Trigger slug (next/prev)
		 */
		self.scrollPlaylist = function( trigger ) {

			var margin = 0,
				videoItem = $( 'ul li.l-pvm-video__item' ).eq( 0 ),
				currentMargin = videoItem.css( 'margin-left' );

			if ( 'next' === trigger ) {

				margin = parseInt( currentMargin, 10 ) - ( 1.5 * listItemSize );
				margin = ( margin < -maxMargin ) ? -maxMargin : margin;

			} else if ( 'prev' === trigger ) {

				margin = parseInt( currentMargin, 10 ) + ( 1.5 * listItemSize );
				margin = ( 0 < margin ) ? 0 : margin;

			} else {
				return;
			}

			videoItem.css( 'margin-left', margin + 'px' );

			// Toggle prev/next triggers.
			nextTrigger.toggleClass( 'is-hidden', margin === -maxMargin );
			$( 'div.l-pvm-video__shadow-right' ).toggleClass( 'is-hidden', margin === -maxMargin );
			prevTrigger.toggleClass( 'is-hidden', 0 === margin );
			$( 'div.l-pvm-video__shadow-left' ).toggleClass( 'is-hidden', 0 === margin );

		};

		/**
		 * Triggers Video play.
		 */
		self.triggerPlay = function( activeSlide ) {
			if ( 0 < activeSlide.find( '.c-pvm-player__link' ).length ) {
				activeSlide.find( '.c-pvm-player__link' ).trigger( 'click' );
			}
		};

		/**
		 * bind the touchstart event to the Playlist
		 */
		self.swipeTouchStart = function( event ) {
			xDown = event.originalEvent.touches[0].clientX;
		};

		/**
		 * Handle the swipe event for the Playlist
		 * @param event
		 */
		self.swipeTouchMove = function( event ) {

			var xUp, xDiff;

			if ( ! xDown ) {
				return;
			}

			xUp = event.originalEvent.touches[0].clientX;

			xDiff = xDown - xUp;

			if ( 10 < Math.abs( xDiff ) ) { /*most significant*/
				if ( 0 > xDiff ) {
					self.scrollPlaylist( 'prev' );
				} else {
					self.scrollPlaylist( 'next' );
				}
			}

			/* reset values */
			xDown = null;
		};

		/**
		 * Callback event when use click on play button.
		 *
		 * @param {object} playerInstance Old player instance if there is any.
		 * @param {string} playerId current player DOM element id.
		 * @param {string} playerType either 'youtube' or 'jwplayer'
		 *
		 * @return {void}
		 */
		self.onClickPlayerLink = function( playerInstance, playerId, playerType ) {

			var playerElement = document.getElementById( jwplayerInstanceDOMID ),
				playerObject = false,
				videoHash = false,
				playlist = false;

			if ( 'undefined' === typeof playerType || 'jwplayer' !== playerType || false !== this.isJWPlayerActive || null === playerElement ) {
				return;
			}

			if ( 'function' !== typeof jwplayer || 'object' !== typeof playerInstance || 'function' !== typeof playerInstance.getConfig ) {
				return;
			}

			playerObject = $( '#' + playerId );
			videoHash = playerObject.data( 'videoid' );
			playlist  = playerObject.data( 'jsonfeed' );

			// First activate player slide.
			this.slider.addClass( 'l-pvm-video__carousel--playing' );
			this.jwplayerSlide.addClass( 'is-active' );

			if ( 'undefined' === typeof playlist || ! playlist ) {
				playlist = 'http://content.jwplatform.com/feeds/' + videoHash + '.json';
			}

			// Setup JWplayer.
			// pmc-video-player plugin loaded
			// all jwplayer ga tracking, etc... are implemented in plugin .setup() call
			this.jwplayerInstance = pmc_jwplayer( jwplayerInstanceDOMID ).setup({
					playlist: playlist,
					ph: 2
				}).instance();

			// Play video.
			this.jwplayerInstance.play( true );

			// Mark flag for player as activate.
			this.isJWPlayerActive = true;

		};

		/**
		 * Deactivate jwplayer instance for carousal.
		 *
		 * @return {void}
		 */
		self.removeJWplayerDOM = function removeJWplayerDOM() {

			if ( true !== this.isJWPlayerActive ) {
				return;
			}

			this.jwplayerSlide.removeClass( 'is-active' );

			// Remove jwPlayer instance.
			this.jwplayerInstance.stop();
			this.jwplayerInstance.remove();

			// Mark flag for player as deactivate.
			this.isJWPlayerActive = false;
			this.slider.removeClass( 'l-video__carousel--playing' );
		};


		// Initialize!
		self.initialize();

	};

	$( function() {
		window.PMCVideoPlaylistItems = new PMCVideoPlaylistItems();
	});

}( window, jQuery ) );
