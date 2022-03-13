/* global ga, pmcGaCustomDimensions */

export default class GoogleAnalytics {
	/**
	 * Google Analytics tracking ID.
	 *
	 * @type {string|null}
	 */
	id = null;

	/**
	 * Named tracker for recording data separately from main analytics account.
	 *
	 * @type {string}
	 */
	trackerName = 'pmcDigitalDaily';

	/**
	 * Document title.
	 *
	 * @type {string}
	 */
	title;

	/**
	 * Device type as detected by main GA integration.
	 *
	 * @type {string}
	 */
	device;

	/**
	 * Constructor.
	 *
	 * @param {string} gaId Google Analytics tracking ID.
	 */
	constructor( gaId ) {
		this.id = gaId;

		this.title = window.document.title;

		this.device = window.pmc_ga_event_tracking?.device || '[D]';

		this.initGA();
	}

	/**
	 * Initialize Google Analytics.
	 */
	initGA() {
		ga( 'create', this.id, 'auto', this.trackerName );

		this.setTitle( this.title );

		if ( 'object' === typeof pmcGaCustomDimensions ) {
			ga( `${ this.trackerName }.set`, pmcGaCustomDimensions );
		}
	}

	/**
	 * Record pageview for given relative URL.
	 *
	 * @param {string}      url   Relative URL.
	 * @param {string|null} title Title to report alongside URL.
	 */
	trackPageview( url, title ) {
		this.setTitle( title );
		ga( `${ this.trackerName }.send`, 'pageview', url );
	}

	/**
	 * Track click event.
	 *
	 * @param {string} url      Click destination.
	 * @param {string} category Event category.
	 */
	recordClick( url, category ) {
		const gaData = {
			hitType: 'event',
			eventCategory: category,
			eventAction: 'click',
			eventLabel: `${ this.device } ${ url }`,
		};

		this.setTitle( this.title );
		ga( `${ this.trackerName }.send`, gaData );
	}

	/**
	 * Set title sent with pageview or event.
	 *
	 * @param {string} title Title to send with request.
	 */
	setTitle( title ) {
		ga( `${ this.trackerName }.set`, 'title', title );
	}
}
