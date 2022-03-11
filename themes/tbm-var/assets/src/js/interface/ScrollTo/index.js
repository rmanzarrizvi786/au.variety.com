import ScrollTo from './ScrollTo';

// Initialize all ScrollTos.
export default function initScrollTo() {
	const ScrollTos = [ ...document.querySelectorAll( '.js-ScrollTo' ) ];

	ScrollTos.forEach( ( el ) => new ScrollTo( el ) );
}
