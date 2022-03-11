import LazyIframe from './LazyIframe';

export default function initLazyIframe() {
	const lazyIframe = [
		...document.querySelectorAll( 'iframe[data-lazy-src]' ),
	];

	if ( lazyIframe.length ) {
		lazyIframe.forEach( ( el ) => {
			new LazyIframe( el );
		} );
	}
}
