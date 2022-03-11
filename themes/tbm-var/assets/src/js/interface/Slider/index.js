/**
 *
 * Slider module from Deadline.
 *
 * This index.js should import modules and invoke any JS functionality.
 *
 */

import Slider from './Slider.js';

( function () {
	const sliders = [ ...document.querySelectorAll( '.js-Slider' ) ];

	const onSafeResize = function () {
		sliders.forEach( ( el ) => {
			if ( undefined === el.pmcSlider ) {
				el.pmcSlider = new Slider( el );
				el.pmcSlider.init();
			} else {
				el.pmcSlider.setVals();
				el.pmcSlider.move();
			}

			if ( undefined !== el.pmcMobileSlider ) {
				el.pmcMobileSlider.destroy();
			}
		} );
	};

	window.addEventListener( 'resize', () => {
		requestAnimationFrame( onSafeResize );
	} );

	document.addEventListener( 'DOMContentLoaded', () => {
		onSafeResize();
	} );
} )();
