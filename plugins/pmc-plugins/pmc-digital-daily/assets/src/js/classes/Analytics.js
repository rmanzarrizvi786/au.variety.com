/* global IntersectionObserver, IntersectionObserverEntry */

import GoogleAnalytics from './lib/GoogleAnalytics';

/**
 * Record analytics data as user interacts with Digital Daily.
 */
export default class Analytics {
	/**
	 * Anchor selectors to track clicks for.
	 *
	 * @type {Array}
	 */
	anchorClickSelectors;

	/**
	 * Block selectors to track clicks for.
	 *
	 * @type {Array}
	 */
	blockClickSelectors;

	/**
	 * Full-view block selectors to track.
	 *
	 * @type {Array}
	 */
	pageviewSelectors;

	/**
	 * Which view is this?
	 *
	 * @type {string}
	 */
	viewType;

	/**
	 * Data attribute containing post object's relative permalink.
	 *
	 * @type {string}
	 */
	permalinkAttr = 'data-permalink';

	/**
	 * Data attribute containing post object's title.
	 *
	 * @type {string}
	 */
	titleAttr = 'data-title';

	/**
	 * Instance of Google Analytics helper.
	 *
	 * @type {GoogleAnalytics}
	 */
	ga;

	/**
	 * Timeout ID used to debounce observer's checks of what's in view.
	 *
	 * @type {boolean|number}
	 */
	intersectTimeout = false;

	/**
	 * Constructor.
	 *
	 * @param {Object} config Analytics configuration.
	 */
	constructor( config ) {
		this.anchorClickSelectors = config.anchorClickSelectors;
		this.blockClickSelectors = config.blockClickSelectors;
		this.pageviewSelectors = config.pageviewSelectors;
		this.viewType = config.viewType;
		this.ga = new GoogleAnalytics( config.gaId );

		this.onAnchorSelectorClick = this.onAnchorSelectorClick.bind( this );
		this.onBlockSelectorClick = this.onBlockSelectorClick.bind( this );
		this.onIntersect = this.onIntersect.bind( this );
		this.initObserver = this.initObserver.bind( this );

		this.recordInitialPageview();
		this.initClickTracking();

		this.initObserver();
	}

	/**
	 * Record pageview for Digital Daily itself.
	 */
	recordInitialPageview() {
		this.ga.trackPageview( window.location.href, document.title );
	}

	/**
	 * Initialize click tracking.
	 */
	initClickTracking() {
		Object.entries( this.anchorClickSelectors ).forEach(
			( [ selector, category ] ) => {
				const elements = document.querySelectorAll( selector );

				elements.forEach( ( element ) =>
					element.addEventListener( 'click', ( event ) =>
						this.onAnchorSelectorClick( event, category )
					)
				);
			}
		);

		Object.entries( this.blockClickSelectors ).forEach(
			( [ selector, category ] ) => {
				const elements = document.querySelectorAll( selector );

				elements.forEach( ( element ) =>
					element.addEventListener( 'click', ( event ) =>
						this.onBlockSelectorClick( event, category )
					)
				);
			}
		);
	}

	/**
	 * Record click event for an anchor selector.
	 *
	 * @param {Event}  event    Event data.
	 * @param {string} category Event category.
	 */
	onAnchorSelectorClick( event, category ) {
		let url;

		if ( 'a' === event.target.nodeName ) {
			url = event.target.href;
		} else {
			const anchor = event.target.closest( 'a' );
			url = anchor.href;
		}

		if ( ! url.length || '#' === url ) {
			return;
		}

		this.ga.recordClick( url, category );
	}

	/**
	 * Record click event for a block-specific selector.
	 *
	 * @param {Event}  event    Event data.
	 * @param {string} category Event category.
	 */
	onBlockSelectorClick( event, category ) {
		const container = event.target.closest( '[data-permalink]' );

		if ( ! container ) {
			return;
		}

		this.ga.recordClick(
			container.getAttribute( this.permalinkAttr ),
			category
		);
	}

	/**
	 * Initialize reading tracking.
	 */
	initObserver() {
		const ioOptions = {
			root: null,
			threshold: [ 0 ],
		};

		const observer = new IntersectionObserver(
			this.onIntersect,
			ioOptions
		);

		this.pageviewSelectors.forEach( ( selector ) => {
			let blocks;

			// Nested selectors, such as for the Columns block, require special handling.
			if ( 'object' === typeof selector ) {
				const [ containerSelector, childSelectors ] = selector;
				blocks = document.querySelectorAll( containerSelector );

				blocks.forEach( ( container ) => {
					const childBlock = container.querySelector(
						childSelectors
					);

					if ( ! childBlock ) {
						return;
					}

					const permalink = childBlock.getAttribute(
						this.permalinkAttr
					);
					const title = childBlock.getAttribute( this.titleAttr );

					container.setAttribute( this.permalinkAttr, permalink );
					container.setAttribute( this.titleAttr, title );
				} );
			} else {
				blocks = document.querySelectorAll( selector );
			}

			blocks.forEach( ( block ) => observer.observe( block ) );
		} );
	}

	/**
	 * Record analytics for a block considered read.
	 *
	 * @param {IntersectionObserverEntry[]} entries DOM objects tracked by IO.
	 */
	onIntersect( entries ) {
		if ( 'full' === this.viewType && !! this.intersectTimeout ) {
			clearTimeout( this.intersectTimeout );
		}

		this.intersectTimeout = setTimeout( () => {
			let inView = entries.filter( ( entry ) => entry.isIntersecting );

			if ( 'full' === this.viewType && inView.length > 1 ) {
				inView.sort(
					( first, second ) =>
						second.intersectionRatio - first.intersectionRatio
				);

				inView = inView.slice( 0, 1 );
			}

			inView.forEach( ( entry ) => {
				if ( ! entry.target.hasAttribute( this.permalinkAttr ) ) {
					return;
				}

				this.ga.trackPageview(
					entry.target.getAttribute( this.permalinkAttr ),
					entry.target.getAttribute( this.titleAttr )
				);
			} );
		}, 500 );
	}
}
