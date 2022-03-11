/**
 *
 * Lazy Load Iframe.
 *
 */

export default class LazyIframe {
	/**
	 * Startup Functionality.
	 *
	 * Set the state for whether this player has the social share
	 * functionality.
	 */

	constructor( el ) {
		if ( null !== el ) {
			el.src = el.dataset.lazySrc;
			el.dataset.lazySrc = 'lazyloaded';
		}
	}
}
