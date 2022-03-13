/* global pmcDigitalDailyConfig, pmcDigitalDailyAnalyticsConfig */

import Analytics from './classes/Analytics';
import CoverAdOverlay from './classes/CoverAdOverlay';
import PrintPreparer from './classes/PrintPreparer';
import ScrollAssist from './classes/ScrollAssist';

/**
 * Initialize plugin namespace within PMC's global.
 */
window.pmc = window.pmc || {};
window.pmc.digitalDaily = {
	config: pmcDigitalDailyConfig,

	scrollAssist: {
		/**
		 * Event triggered by ScrollAssist after programmatic scrolling stops.
		 *
		 * @type {string}
		 */
		event: 'pmc.digitalDaily.scrollAssist.afterScroll',

		/**
		 * Instance of ScrollAssist class.
		 *
		 * @type {ScrollAssist}
		 */
		instance: null,
	},
};

/**
 * Check if current issue has a Cover Ad, which invokes the `CoverAdOverlay`
 * class and prevents `ScrollAssist` from intercepting the scroll experience.
 *
 * @return {boolean} If issue has cover ad.
 */
window.pmc.digitalDaily.issueHasCoverAd = () => {
	return (
		! Boolean( window.pmc.digitalDaily.config.isFullView ) &&
		Boolean( window.pmc.digitalDaily.config.coverAd.has )
	);
};

/**
 * Calculate height of various header elements to determine offset that will
 * prevent header from overlapping content.
 *
 * @param {boolean} useScrollHeight Determine offset from scrollHeight rather
 *                                  than computed height.
 * @return {number} Header elements height in pixels.
 */
window.pmc.digitalDaily.getTopOffset = ( useScrollHeight = true ) => {
	const obscuringElements = document.querySelectorAll(
		'#wpadminbar, div.js-Header-contents, div.header-sticky-nav, div.digital-daily-navigation'
	);

	let topOffset = 0;

	obscuringElements.forEach( ( topEl ) => {
		const style = window.getComputedStyle( topEl );

		if ( 'none' === style.display ) {
			return;
		}

		if ( useScrollHeight ) {
			topOffset += topEl.scrollHeight;
		} else {
			topOffset += topEl.offsetHeight;
			topOffset += parseInt( style.marginTop, 10 );
			topOffset += parseInt( style.marginBottom, 10 );
		}
	} );

	return topOffset;
};

/**
 * Load analytics if conditions permit.
 *
 * @param {Object} config Plugin configuration.
 */
const initAnalytics = ( config ) => {
	if ( 'function' !== typeof ga || ! config.gaId ) {
		return;
	}

	if (
		! config.blockClickSelectors?.length &&
		! config.pageviewSelectors?.length
	) {
		return;
	}

	new Analytics( config );
};

/**
 * Make print preparer available to code that triggers browsers' print ability.
 */
const initPrintPreparer = () => {
	window.pmc.printPreparer = new PrintPreparer();
};

/**
 * Load scroll handling.
 */
const initScrollAssist = () => {
	window.pmc.digitalDaily.scrollAssist.instance = new ScrollAssist();
};

/**
 * Load cover-ad-overlay handling when conditions warrant it.
 */
const initCoverAdOverlay = () => {
	if ( ! window.pmc.digitalDaily.issueHasCoverAd() ) {
		return;
	}

	new CoverAdOverlay( window.pmc.digitalDaily.config.coverAd );
};

initAnalytics( pmcDigitalDailyAnalyticsConfig ?? {} );
initScrollAssist();
initCoverAdOverlay();
initPrintPreparer();
