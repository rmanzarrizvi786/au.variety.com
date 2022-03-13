/* eslint-disable */

export default class VideoCrop {

	constructor ( el ) {
		this.el = el;
		this.handleClick = this.handleClick.bind( this );
		this.jwP = el.querySelectorAll( '[id^="jwplayer_"][id$="_div"]' );
		this.JWPlaylists = {};

		this.jwP.forEach( el => this.setJWPlaylist( el ) );
		this.el.addEventListener( 'click', this.handleClick );

		this.isGallery = false;
		this.jwplayerParentElement = false;
		this.jwplayerElementDOMID = false;
		this.jwplayerInstance = false;
		this.isJWPlayerActive = false;
	}

	setJWPlaylist ( el ) {
		let videoID = el.getAttribute( 'id' );
		try {
			this.JWPlaylists[ videoID ] =
				"https://content.jwplatform.com/feeds/" +
				el.getAttribute( "id" ).split( "_" )[ 1 ] +
				".json";
		} catch ( e ) {}
	}

	handleClick ( e ) {
		e.preventDefault();
		this.setVideo( this.el );
		this.el.removeEventListener( 'click', this.handleClick );
	}

	setVideo ( el ) {
		const jwPlayer = el.querySelector( '[id^="jwplayer_"][id$="_div"]' );

		if ( 'undefined' !== typeof jwPlayer && null !== jwPlayer ) {
			this.playJW( jwPlayer );
			return;
		}

		// Before resetting player clone necessary element.
		const iframe = el.querySelector( 'iframe[data-src*="youtu"]' );

		// Remove JWPlayer if current player is not JWPlayer.
		this.resetPlayers();

		if ( 'undefined' !== typeof iframe && null !== iframe ) {
			this.playYoutube( iframe );
		}
	}

	playYoutube ( iframe ) {
		this.getSrc( iframe, src => {
			const newEl = this.setPlayerEl( iframe );
			newEl.setAttribute( 'src', src );
		} );
	}

	playJW ( el ) {

		// We need to do this first, if not, this function will cause setPlayerEl on cloned node to get revert.
		var newplayer = '';

		const id = el.getAttribute('id');

		let playlist = this.JWPlaylists[ id ];

		if ( false === playlist ) {
			playlist = el.getAttribute( 'data-jsonfeed' );
		}

		if ( 'undefined' === typeof playlist || '' === playlist ) {
			return;
		}

		/**
		 * If it's gallery then use instance that already created.
		 */
		if ( 'undefined' !== typeof( this.isGallery ) && true === this.isGallery ) {

			if ( null === document.getElementById( this.jwplayerElementDOMID ) ) {
				return;
			}

			this.resetPlayers();

			this.el.setAttribute('hidden', '');
			this.jwplayerParentElement.removeAttribute('hidden');

			if ( ! this.jwplayerInstance && this.jwplayerElementDOMID ) {
				if ( window.pmc_jwplayer ) {
					// pmc-video-player plugin loaded,
					// all jwplayer ga tracking, etc... are implemented in plugin .setup() call
					this.jwplayerInstance = window.pmc_jwplayer( this.jwplayerElementDOMID );
				} else if ( window.jwplayer ) {
					// pmc-video-player plugin wasn't loaded, fall back to native jwplayer, no jwplayer ga tracking
					this.jwplayerInstance = window.jwplayer( this.jwplayerElementDOMID );
				}
			}

			if ( ! this.jwplayerInstance ) {
				return;
			}

			this.jwplayerInstance.setup( {
				playlist: playlist,
				ph: 2
			} );

			this.jwplayerInstance.play();
			this.isJWPlayerActive = true;

			return;
		}

		const newEl = this.setPlayerEl( el );
		const newId = id + ( new Date() ).getTime();
		newEl.setAttribute( 'id', newId );
		let newPlayerInstance = undefined;

		if ( window.pmc_jwplayer ) {
			// pmc-video-player plugin loaded
			// all jwplayer ga tracking, etc... are implemented in plugin .setup() call
			newPlayerInstance = window.pmc_jwplayer( newId );
		} else if ( window.jwplayer ) {
			// pmc-video-player plugin wasn't loaded, fall back to native jwplayer, no jwplayer ga tracking
			newPlayerInstance = window.jwplayer( newId );
		}

		newPlayerInstance.setup( {
			playlist: playlist,
			ph: 2
		} );

		newPlayerInstance.play();

	}

	/**
	 * To reset all players.
	 */
	resetPlayers() {

		if ( true === this.isJWPlayerActive ) {
			this.jwplayerInstance.pause();
			this.jwplayerParentElement.setAttribute('hidden', '');
			this.isJWPlayerActive = false;
		}

		this.el.innerHTML = '';
		this.el.removeAttribute( 'hidden' );

	}

	setPlayerEl ( el ) {
		const clonedEl = el.cloneNode();
		this.el.innerHTML = '';
		this.el.appendChild( clonedEl );
		return clonedEl;
	}

	getSrc ( el, cb ) {
		let src = el.dataset.src || el.getAttribute( 'src' ) || '';

		// Add `autoplay=1` to the `src` so that the user doesn't have to click twice.
		if ( '' !== src ) {
			if ( -1 !== src.indexOf( 'autoplay' ) ) {
				src = src.replace( /autoplay=[01]/i, 'autoplay=1' );
			} else {
				src = `${src}&autoplay=1`;
			}

			cb( src );
		}
	}
}
