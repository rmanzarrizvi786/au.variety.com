/* globals Event */

/**
 * Improve scrolling experience and dispatch an event when scrolling stops.
 *
 * Used primarily to prevent Analytics from tracking events from programmatic
 * scrolls.
 */
export default class ScrollAssist {
	/**
	 * Timeout used to track scrolling.
	 *
	 * @type {number|null}
	 */
	tracker = null;

	/**
	 * Constructor.
	 */
	constructor() {
		this.dispatchEvent = this.dispatchEvent.bind( this );
		this.doSmoothScroll = this.doSmoothScroll.bind( this );

		this.dispatchEventAfterScroll = this.dispatchEventAfterScroll.bind(
			this
		);
		this.startTrackingScroll = this.startTrackingScroll.bind( this );
		this.dispatchEventOnScrollStop = this.dispatchEventOnScrollStop.bind(
			this
		);

		/**
		 * Scrolling is disabled when cover ad is present. After cover ad loads,
		 * `this.doSmoothScroll` is invoked by the `CoverAdOverlay` class.
		 */
		if ( ! window.pmc.digitalDaily.issueHasCoverAd() ) {
			window.addEventListener( 'DOMContentLoaded', () => {
				// Impose slight delay, otherwise Safari refuses to scroll.
				setTimeout( this.doSmoothScroll, 5 );
			} );
		}

		this.showOverlay();
	}

	/**
	 * Dispatch event indicating scrolling has stopped.
	 */
	dispatchEvent() {
		const event = new Event( window.pmc.digitalDaily.scrollAssist.event );

		window.dispatchEvent( event );
	}

	/**
	 * Improve anchor scrolling.
	 *
	 * Initiates scroll earlier than the browser does and accounts for header
	 * elements that would otherwise overlay the top of the scroll target.
	 */
	doSmoothScroll() {
		// Data not used in a way that presents XSS vulnerability.
		// phpcs:disable WordPressVIPMinimum.JS.Window.location
		if ( ! window.location.hash ) {
			this.dispatchEvent();
			return;
		}

		// Our GA tracking may add query-string style data to the hash.
		const hash = window.location.hash.substring( 1 ).split( '&' );

		if ( ! hash ) {
			this.dispatchEvent();
			return;
		}

		const scrollTarget = document.getElementById( hash[ 0 ] );

		// Stop browser from trying to scroll again.
		window.location.hash = '';

		if ( ! scrollTarget ) {
			this.dispatchEvent();
			return;
		}

		const wrapperElement = scrollTarget.closest( '.article-block-outer' );

		const wrapperElementTopOffset = wrapperElement.offsetTop;

		if ( ! wrapperElement ) {
			this.dispatchEvent();
			return;
		}

		const scrollTargetBox = wrapperElement.getBoundingClientRect();

		const scrollTargetBoxStyle = window.getComputedStyle( wrapperElement );

		const scrollTargetBoxTop =
			scrollTargetBox.top +
			parseInt( scrollTargetBoxStyle.marginTop, 10 );

		let topOffset = window.pmc.digitalDaily.getTopOffset( false );

		const pageHtml = document.querySelector( 'html' );

		// Add header height to topOffset for compensating the page shift to top because of header going display none.
		if (
			! pageHtml.classList.contains( 'is-sticky' ) &&
			wrapperElementTopOffset > 50
		) {
			const headerHeight = document.querySelector( '.js-Header-contents' )
				.offsetHeight;
			topOffset += headerHeight;
		}

		this.dispatchEventAfterScroll();

		window.scrollTo( {
			top: scrollTargetBoxTop - topOffset,
			left: 0,
			// `behavior` must be smooth; `auto` is unreliable.
			behavior: 'smooth',
		} );
		// phpcs:enable WordPressVIPMinimum.JS.Window.location
	}

	/**
	 * Trigger event after scrolling stops.
	 */
	dispatchEventAfterScroll() {
		window.addEventListener( 'scroll', this.startTrackingScroll, {
			passive: true,
		} );
	}

	/**
	 * Track programmatic scrolling to detect when it stops, as there's no event
	 * fired when scrolling stops.
	 */
	startTrackingScroll() {
		if ( null !== this.tracker ) {
			clearTimeout( this.tracker );
		}

		this.tracker = setTimeout( this.dispatchEventOnScrollStop, 50 );
	}

	/**
	 * Run requested callback when scrolling stops.
	 */
	dispatchEventOnScrollStop() {
		window.removeEventListener( 'scroll', this.startTrackingScroll, {
			passive: true,
		} );

		this.tracker = null;

		this.dispatchEvent();
	}

	/**
	 * Show overlay on page load, revealing content after scroll completes.
	 */
	showOverlay() {
		if ( ! window.pmc.digitalDaily.config.isFullView ) {
			return;
		}

		const hiddenClass = 'lrv-a-hidden';
		const overlayEle = document.getElementById(
			'pmc-digital-daily-cover-ad-overlay'
		);

		if ( '' !== window.location.hash.substring( 1 ) ) {
			overlayEle.classList.remove( hiddenClass );
		}

		window.addEventListener(
			window.pmc.digitalDaily.scrollAssist.event,
			() => {
				setTimeout( () => {
					overlayEle.classList.add( hiddenClass );
				}, 250 );
			}
		);
	}
}
