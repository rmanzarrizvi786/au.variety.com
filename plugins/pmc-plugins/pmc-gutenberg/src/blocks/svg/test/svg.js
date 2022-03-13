import { svg } from '../svg.js';

describe( 'PMC SVG block tests', () => {
	it( 'returns null for the save method', () => {
		expect( svg.save() ).toBe( null );
	} );
} );
