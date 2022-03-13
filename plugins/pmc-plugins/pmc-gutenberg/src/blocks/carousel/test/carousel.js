import { carousel } from '../carousel.js';

describe( 'PMC Carousel block tests', () => {
	it( 'returns null for the save method', () => {
		expect( carousel.save() ).toBe( null );
	} );
} );
