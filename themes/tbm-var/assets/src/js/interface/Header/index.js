import Header from './Header.js';

export default function initHeader() {
	// TODO: For Lara, This maybe should be turned into a class
	const headers = [ ...document.querySelectorAll( '.js-Header' ) ];
	headers.forEach( ( el ) => {
		if ( undefined === el.pmcHeader ) {
			new Header( el );
		}
	} );
}
