import Flickity from '@npm/flickity';

export default function initFlickity() {
	const carousels = [ ...document.querySelectorAll( '.js-Flickity' ) ];

	carousels.forEach( ( el ) => {
		// Adds support for additional JSON settings via HTML attribute "data-flickity"
		let additionalSettings = {};

		try {
			additionalSettings = JSON.parse( el.dataset.flickity );
		} catch ( e ) {
			if ( 'undefined' !== typeof console ) {
				console.log( 'Invalid JSON' ); // eslint-disable-line no-console
			}
		}

		const isContained = el.classList.contains( 'js-Flickity--isContained' )
			? true
			: false;

		const slider = new Flickity(
			el,
			Object.assign(
				{
					cellSelector: '.js-Flickity-cell',
					pageDots: false,
					imagesLoaded: true,
					groupCells: true,
					contain: isContained,
					arrowShape: {
						x0: 10,
						x1: 60,
						y1: 50,
						x2: 65,
						y2: 45,
						x3: 20,
					},
				},
				additionalSettings
			)
		);

		return slider;
	} );
}
