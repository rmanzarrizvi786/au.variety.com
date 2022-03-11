/* eslint-disable no-unused-vars, no-unused-expressions */

import SiteHeaderManager from './SiteHeaderManager';
import UberMenu from './UberMenu';

require( '../scss/style.scss' );

if ( 'undefined' === typeof window.$ ) {
	window.$ = window.jQuery;
}

'use strict';

$( () => {

	// Initialize site header manager.
	SiteHeaderManager.init();

	//UberMenu
	UberMenu.start();
});

/* eslint-enable */
