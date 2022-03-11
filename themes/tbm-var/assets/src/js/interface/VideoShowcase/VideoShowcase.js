import { delegateEvent } from '@js/utils/dom';
import Collapsible from '@js/interface/Collapsible/Collapsible';

/**
 *
 * Video Showcase.
 *
 * This module is heavily inspired by the VideoGallery module in the Rolling Stone. This is
 * version 2 of its descendent, Video Showcase, that was first written in Robb Report to support
 * Youtube only. This is a more state-forward approach of VideoShowcase that also supports JWPlayer.
 *
 */

export default class VideoShowcase {
	constructor( el ) {
		this.el = el;

		// All triggers that contain video IDs to be played on click.
		this.triggers = [
			...el.querySelectorAll( '[data-video-showcase-trigger]' ),
		];

		// The main player that also, optionally may be a trigger. It should contain an iframe and #jwplayerContainer.
		this.player = el.querySelector( '[data-video-showcase-player]' );

		// Elements within the player that will be hidden after the first play.
		this.elementsToHide = [
			...this.el.querySelectorAll( '.is-to-be-hidden' ),
		];

		// Attributes that will be removed from the player once it is initialized. Only relevant for players that are
		// also triggers.
		this.attributesToRemoveFromPlayer = [
			'data-video-showcase-trigger',
			'data-video-showcase-title',
			'data-video-showcase-dek',
			'data-video-showcase-permalink',
			'data-video-showcase-type',
			'href',
		];

		/**
		 * State.
		 *
		 * @type {Object}
		 * @property {boolean} isPlayerSetup - Whether or not the player has been setup or played. Set in onFirstTimePlay()
		 * @property {string} videoID - A Youtube or JWPlayer ID extracted from a `data-video-showcase-trigger` e.g. f1FX5wvC3DA
		 * @property {string} videoType - "youtube" or "jwplayer" from `data-video-showcase-type`
		 */
		this.state = {
			isPlayerSetup: false,
			hasSocialShare: false,
			videoID: '',
			videoType: '',
		};

		/**
		 * Player UI.
		 *
		 * @type {Object}
		 * @property {string} heading - A anchor element that will receive both a permalink and heading text.
		 * @property {string} dek - An element containing a direct child paragraph that will support the dek text.
		 * @property {string} iframe - The iframe that will recieve an src when a trigger with the Youtube video type is clicked.
		 * @property {string} jwplayerContainer - The placeholder element where JWPlayer will be applied.
		 * @property {string} social - The main social share container that will be replaced with social share from triggers.
		 */
		this.playerUI = {
			heading: el.querySelector(
				'[data-video-showcase-player-heading], .js-VideoShowcasePlayerHeading'
			),
			sponsoredBadge: el.querySelector(
				'.js-video-showcase-sponsored-badge'
			),
			dek: el.querySelector(
				'[data-video-showcase-player-dek], .js-VideoShowcasePlayerDek'
			),
			iframe: el.querySelector(
				'[data-video-showcase-iframe], .js-VideoShowcasePlayerIframe'
			),
			jwplayerContainer: el.querySelector( '#jwplayerContainer' ),
			social: el.querySelector(
				'[data-video-showcase-player-social-share], .js-VideoShowcasePlayerSocialShare'
			),
			oembedContainer: el.querySelector(
				'[data-video-showcase-oembed], .js-VideoShowcasePlayerOembed'
			),
			time: el.querySelector( '.js-VideoShowcasePlayerTime' ),
		};

		this.init();

		if ( this.player.dataset.videoShowcaseAutoplay ) {
			this.handleTriggerClick( null, this.triggers[ 0 ] );
		} else {
			delegateEvent(
				this.el,
				'click',
				'[data-video-showcase-trigger]',
				this.handleTriggerClick.bind( this )
			);
		}
	}

	/**
	 * Startup Functionality.
	 *
	 * Set the state for whether this player has the social share
	 * functionality.
	 */

	init() {
		if ( null !== this.playerUI.social ) {
			this.state.hasSocialShare = true;
		}
	}

	/**
	 * Get Player Card Data.
	 *
	 * Set up a scaffold object that contains data from the trigger that will be applied
	 * to the main player card. These are all strings from data attributes except the
	 * social share, which replaces an entire block of HTML.
	 *
	 * @param {string} el - A trigger.
	 *
	 * @return {Object} - An object containing the data needed to update the player.
	 * @property {string} title - Title text from the `data-video-showcase-title`
	 * @property {string} dek - Dek text from the `data-video-showcase-dek`
	 * @property {string} permalink - Link from `data-video-showcase-permalink`
	 * @property {string} socialString - HTML string returned from wp.template.
	 */

	getPlayerCardData( el ) {
		const triggerID = el.dataset.videoShowcaseTrigger;
		const hasSocialShare = this.state.hasSocialShare;

		return {
			title: el.dataset.videoShowcaseTitle,
			sponsored: el.dataset.videoShowcaseSponsored,
			dek: el.dataset.videoShowcaseDek,
			permalink: el.dataset.videoShowcasePermalink,
			time: el.dataset.videoShowcaseTime,
			socialString: ( function ( data ) {
				if ( window.wp && hasSocialShare ) {
					const template = wp.template(
						`trigger-social-share-${ triggerID }`
					);
					return template( data );
				}
			} )(),
		};
	}

	/**
	 * Update Player Card Data.
	 *
	 * Apply the assembled data to the UI.
	 *
	 * @param {string} el - A trigger.
	 * @param {Object} data - An object of data from getPlayerCardData.
	 */

	updatePlayerCardData( el, data ) {
		if ( this.playerUI.heading && data.title ) {
			this.playerUI.heading.innerText = data.title;
		}

		if ( this.playerUI.heading && data.permalink ) {
			this.playerUI.heading.setAttribute( 'href', data.permalink );
		}

		if ( this.playerUI.dek && data.dek ) {
			this.playerUI.dek.innerText = data.dek;
		}

		if ( this.playerUI.time && data.time ) {
			this.playerUI.time.innerText = data.time;
		}

		if ( data.socialString && this.state.hasSocialShare ) {
			this.updateCardSocialShare( data.socialString );
		}

		if ( this.playerUI.sponsoredBadge ) {
			if ( data.sponsored ) {
				this.playerUI.sponsoredBadge.classList.remove( 'u-hidden' );
			} else {
				this.playerUI.sponsoredBadge.classList.add( 'u-hidden' );
			}
		}
	}

	updateCardSocialShare( html ) {
		this.playerUI.social.removeChild(
			this.playerUI.social.querySelector( 'ul' )
		);

		// NOTE: html comes from JS template with escaped data.
		this.playerUI.social.insertAdjacentHTML( 'beforeend', html );

		this.initCollapsible(
			this.playerUI.social.querySelector( '[data-collapsible]' )
		);
	}

	initCollapsible( el ) {
		el.pmcCollapsible = new Collapsible( el );
	}

	/**
	 * Return URL.
	 *
	 * Return an embed URL with the video ID based on the type of video.
	 *
	 * @param {string} id - ID of the video e.g. f1FX5wvC3DA
	 * @param {string} type - "youtube" or "jwplayer"
	 */
	returnUrl( id, type ) {
		if ( 'youtube' === type ) {
			return `https://www.youtube.com/embed/${ id }`;
		}

		if ( 'jwplayer' === type ) {
			return `https://content.jwplatform.com/feeds/${ id }.json`;
		}

		if ( 'oembed' === type ) {
			return id;
		}
	}

	/**
	 * Remove hidden attribute from the iframe and set the src.
	 *
	 * @param {string} youtubeUrl - A Youtube embed URL from returnUrl.
	 */
	playYoutube( youtubeUrl ) {
		this.playerUI.iframe.removeAttribute( 'hidden' );
		this.playerUI.iframe.setAttribute(
			'src',
			`${ youtubeUrl }?rel=0&autoplay=1&showinfo=0&controls=2&rel=0&modestbranding=0`
		);
		this.playerUI.iframe.setAttribute(
			'allow',
			'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture'
		);
	}

	/**
	 * Remove hidden attribute from jwplayerContainer and play the video with the jwplayer API.
	 * Note that `playlist` refers to a JWPlayer media object where the videos are handled within
	 * JWPlayer not the playlist taxonomy in WordPress.
	 *
	 * If there is something amiss, this is a good place to trouble shoot - it's possible we need to
	 * getPlaylist() first to retrieve an individual video, then play it, but this was working.
	 *
	 * @see https://developer.jwplayer.com/jw-player/docs/developer-guide/customization/configuration-reference/#playlist
	 *
	 * @param {string} jwplayerUrl - A Youtube embed URL from returnUrl.
	 */
	playJW( jwplayerUrl ) {
		let playerInstance;

		this.playerUI.jwplayerContainer.removeAttribute( 'hidden' );

		if ( window.pmc_jwplayer ) {
			playerInstance = window.pmc_jwplayer(
				this.playerUI.jwplayerContainer.id,
				'default'
			);
		} else if ( window.jwplayer ) {
			playerInstance = window.jwplayer(
				this.playerUI.jwplayerContainer.id
			);
		}

		if ( playerInstance ) {
			playerInstance.setup( {
				playlist: jwplayerUrl,
				aspectratio: '16:9',
			} );
			playerInstance.play();
		}
	}

	/**
	 * Remove hidden attribute from the iframe and set the src.
	 *
	 * @param {string} html - oembed html returned by WP
	 */
	playEmbed( html ) {
		this.playerUI.oembedContainer.removeAttribute( 'hidden' );
		this.playerUI.oembedContainer.innerHTML = '';
		this.playerUI.oembedContainer.insertAdjacentHTML( 'beforeend', html );
	}

	/**
	 * Trigger Click Handler.
	 *
	 * Reset player from previous state, update state and player UI, play the video.
	 *
	 * @param {Event} e
	 * @param {string} el - Clicked trigger element.
	 */
	handleTriggerClick( e, el ) {
		if ( e ) {
			e.preventDefault();
		}

		const previousVideoType = this.state.videoType;

		this.state.videoType = el.dataset.videoShowcaseType;
		this.state.videoID = el.dataset.videoShowcaseTrigger;

		this.resetPlayer( previousVideoType );
		this.playVideo( this.state.videoID, this.state.videoType );
		this.updatePlayerUI( this.state.videoID );
		this.onFirstTimePlay();
	}

	/**
	 * Play the video.
	 *
	 * A wrapper function to conditonally play videos according to their type.
	 *
	 * @param {string} id - Youtube or JWplayer ID, should be from this.state.videoID, e.g. f1FX5wvC3DA
	 * @param {string} type - "youtube" or "jwplayer"
	 */
	playVideo( id, type ) {
		const url = this.returnUrl( id, type );

		if ( 'youtube' === type ) {
			this.playYoutube( url );
		}

		if ( 'jwplayer' === type ) {
			this.playJW( url );
		}

		if ( 'oembed' === type ) {
			this.playEmbed( url );
		}
	}

	// Remove any trigger-related data attributes and hide any elements that are not relevant for the player.
	onFirstTimePlay() {
		if ( false === this.state.isPlayerSetup ) {
			this.elementsToHide.forEach( ( e ) =>
				e.setAttribute( 'hidden', '' )
			);
			this.attributesToRemoveFromPlayer.forEach( ( attr ) =>
				this.player.parentNode.removeAttribute( attr )
			);
			this.state.isPlayerSetup = true;
		}
	}

	/**
	 * Update the UI.
	 *
	 * Replace the heading and dek elements and mark the active trigger.
	 *
	 * @param {string} id - Youtube or JWplayer ID, should be from this.state.videoID, e.g. f1FX5wvC3DA
	 */
	updatePlayerUI( id ) {
		const clickedTrigger = this.el.querySelector(
			`[data-video-showcase-trigger="${ id }"]`
		);
		const data = this.getPlayerCardData( clickedTrigger );

		this.setActiveTrigger( id );
		this.updatePlayerCardData( clickedTrigger, data );
	}

	/**
	 * Reset Player.
	 *
	 * Hide both players and either remove JWPlayer or reset the src for the iframe according
	 * to the previous type of video played.
	 *
	 * @todo this could check for the current type and only run if the current type is not the
	 * same as the past type.
	 *
	 * @param {string} pastType - Youtube or JWplayer ID, should be from this.state.videoID, e.g. f1FX5wvC3DA
	 */
	resetPlayer( pastType ) {
		if ( 'jwplayer' === pastType && window.jwplayer ) {
			window.jwplayer( 'jwplayerContainer' ).remove();
			this.playerUI.jwplayerContainer.setAttribute( 'hidden', '' );
		}

		if ( 'youtube' === pastType ) {
			this.playerUI.iframe.setAttribute( 'src', '' );
			this.playerUI.iframe.setAttribute( 'hidden', '' );
		}
	}

	// Remove `is-playing` class from all triggers.
	resetAllTriggers() {
		this.triggers.forEach( ( el ) => el.classList.remove( 'is-playing' ) );
	}

	/**
	 * Reset triggers and mark the current active trigger.
	 *
	 * @param {string} id - Youtube or JWplayer ID, should be from this.state.videoID, e.g. f1FX5wvC3DA
	 */
	setActiveTrigger( id ) {
		const trigger = this.el.querySelector(
			`.related-videos [data-video-showcase-trigger="${ id }"]`
		);

		this.resetAllTriggers();

		if ( null !== trigger ) {
			trigger.classList.add( 'is-playing' );
		}
	}
}
