import initTabsManager from '@larva-js/interface/TabsManager';

import initLatestNewsButton from '../src/js/interface/LatestNewsButton';

window.addEventListener( 'DOMContentLoaded', () => {
	initTabsManager();
	if ( document.body.classList.contains( 'pmc-mobile' ) ) {
		initLatestNewsButton();
	}
} );
