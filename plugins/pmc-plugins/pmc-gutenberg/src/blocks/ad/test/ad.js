import { ad } from '../ad.js';

describe( 'PMC Ad block tests', () => {
	it( 'returns null for the save method', () => {
		expect( ad.save() ).toBe( null );
	} );
} );
