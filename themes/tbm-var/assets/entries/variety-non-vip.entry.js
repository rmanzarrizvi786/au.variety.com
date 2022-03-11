import LoginStatus from '../src/js/interface/LoginStatus/LoginStatus';

window.addEventListener( 'load', () => {
	if ( undefined !== window.pmc && undefined !== jQuery ) {
		LoginStatus.init();
	}
} );
