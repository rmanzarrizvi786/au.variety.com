/* globals blogherads */

/**
 * Manipulate overlay shown when a cover ad is configured for a given issue.
 */
export default class CoverAdOverlay {
	/**
	 * Configuration passed from PHP.
	 *
	 * @type {Object}
	 */
	config;

	/**
	 * Larva class used to prevent scrolling.
	 *
	 * @type {string}
	 */
	overflowClass = 'lrv-u-overflow-hidden';

	/**
	 * Larva class used to hide overlay.
	 *
	 * @type {string}
	 */
	hiddenClass = 'lrv-a-hidden';

	/**
	 * Overlay Element object.
	 *
	 * @type {Element}
	 */
	overlayElement = null;

	/**
	 * Flag indicating that overlay has been hidden.
	 *
	 * @type {boolean}
	 */
	done = false;

	/**
	 * Constructor.
	 *
	 * @param {Object} config Configuration passed from PHP.
	 */
	constructor( config ) {
		this.config = config;

		this.addEvents = this.addEvents.bind( this );
		this.showOverlay = this.showOverlay.bind( this );
		this.hideOverlay = this.hideOverlay.bind( this );

		this.overlayElement = document.getElementById( this.config.overlayId );

		if ( null === this.overlayElement ) {
			this.hideOverlay();
			return;
		}

		this.showOverlay();
		this.addEvents();
	}

	/**
	 * Add listeners for Boomerang events used to invoke functionality.
	 */
	addEvents() {
		// If no ad renders, hide overlay.
		blogherads.adq.push( () => {
			blogherads.addEventListener( 'gptSlotRenderEnded', ( event ) => {
				if ( ! this.isOverlayUnit( event.slot.domId ) ) {
					return;
				}

				if ( ! Boolean( event.isEmpty ) ) {
					return;
				}

				this.hideOverlay();
			} );
		} );

		// Hide overlay after ad has loaded.
		blogherads.adq.push( () => {
			blogherads.addEventListener( 'gptSlotOnload', ( event ) => {
				if ( ! this.isOverlayUnit( event.slot.domId ) ) {
					return;
				}

				this.hideOverlay();
			} );
		} );
	}

	/**
	 * Check if given ad unit is that of the overlay ad.
	 *
	 * @param {string} domId Ad-unit DOM ID.
	 * @return {boolean} If given ad unit is the overlay ad.
	 */
	isOverlayUnit( domId ) {
		return domId.indexOf( this.config.adDomSlug ) > -1;
	}

	/**
	 * Reveal overlay.
	 */
	showOverlay() {
		document.body.classList.add( this.overflowClass );
		this.overlayElement.classList.remove( this.hiddenClass );

		setTimeout( this.hideOverlay, this.config.timeoutSeconds * 1000 );
	}

	/**
	 * Hide overlay and trigger `ScrollAssist`.
	 */
	hideOverlay() {
		if ( this.done ) {
			return;
		}

		this.done = true;

		if ( null !== this.overlayElement ) {
			this.overlayElement.classList.add( this.hiddenClass );
		}

		document.body.classList.remove( this.overflowClass );

		window.pmc.digitalDaily.scrollAssist.instance.doSmoothScroll();
	}
}
