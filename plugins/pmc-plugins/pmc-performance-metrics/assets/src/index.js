/* globals ga, safari */

import { getCLS, getFCP, getFID, getLCP, getTTFB } from 'web-vitals';

/**
 * Should this request be sent to GA?
 *
 * @return {boolean}
 */
const canSendToGA = () => {
	const { sendToGA } = window.pmc.webVitals.config;

	return sendToGA && 'function' === typeof ga;
};

/**
 * Detect if browser is Safari, to allow toggling between events used to send
 * analytics to GA. Safari does not reliably send the `visibilitychange` event,
 * so we must use `pagehide` instead.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Document/visibilitychange_event
 * @see https://stackoverflow.com/a/9851769
 *
 * @return {boolean}
 */
const isSafari = () => {
	return ( ( testObject ) =>
		'[object SafariRemoteNotification]' === testObject.toString() )(
		! window.safari ||
			( 'undefined' !== typeof safari && safari.pushNotification )
	);
};

/**
 * Google Analytics metrics must be integers, so the value is rounded.
 * For CLS the value is first multiplied by 10000 for greater precision
 * (note: increase the multiplier for greater precision if needed).
 *
 * @see https://github.com/GoogleChrome/web-vitals#using-analyticsjs
 *
 * @param {string} name  Metric name.
 * @param {number} delta Metric magnitude of change.
 * @return {number}
 */
const parseValue = ( name, delta ) => {
	return Math.round( 'CLS' === name ? delta * 10000 : delta );
};

/**
 * Queue a metric for reporting.
 *
 * @param {Object} metric Reporting metric.
 */
const queue = ( metric ) => {
	const { name } = metric;

	window.pmc.webVitals.queue[ name ] = metric;
};

/**
 * Report a metric via a Google tool.
 *
 * @param {Object} queuedMetric       Details for queued metric
 * @param {string} queuedMetric.id    Metric ID.
 * @param {string} queuedMetric.name  Metric name.
 * @param {number} queuedMetric.delta Metric magnitude of change.
 */
const report = ( { id, name, delta } ) => {
	const { eventCategory, tracker } = window.pmc.webVitals.config;
	const eventData = {
		eventCategory,
		eventAction: name,
		eventValue: parseValue( name, delta ),
		eventLabel: id,
		nonInteraction: true,
	};

	if ( canSendToGA() ) {
		ga( `${ tracker }.send`, 'event', eventData );
	} else {
		// eslint-disable-next-line no-console
		console.info( eventData );
	}
};

/**
 * Process captured metrics when page unloads, without using the `unload` event,
 * as it interferes with the back-foward cache.
 *
 * @see https://web.dev/bfcache/
 */
const processOnUnload = () => {
	const {
		config: { gaId, tracker },
		queue: queuedMetrics,
	} = window.pmc.webVitals;

	if ( canSendToGA() ) {
		ga( 'create', gaId, 'auto', tracker );
		ga( `${ tracker }.set`, 'transport', 'beacon' );
	}

	Object.values( queuedMetrics ).map( report );
};

// Queue metrics for reporting.
window.addEventListener( 'DOMContentLoaded', () => {
	// Determine if this visit's metrics will be recorded.
	if ( ! window.pmc.webVitals.config.blockGA ) {
		const { sampleThreshold } = window.pmc.webVitals.config;
		const recordSample = () => {
			const iterations = 3;
			let i = 0;
			let randVal = 0;

			while ( i < iterations ) {
				randVal += Math.random();
				i++;
			}

			randVal = randVal / iterations;

			return randVal <= 1 / sampleThreshold;
		};

		window.pmc.webVitals.config.sendToGA = recordSample();
	}

	// Queue all the things!
	getCLS( queue );
	getFCP( queue );
	getFID( queue );
	getLCP( queue );
	getTTFB( queue );
} );

// Report queued metrics.
if ( isSafari() ) {
	window.addEventListener( 'pagehide', processOnUnload );
} else {
	document.addEventListener( 'visibilitychange', () => {
		if ( 'hidden' === document.visibilityState ) {
			processOnUnload();
		}
	} );
}
