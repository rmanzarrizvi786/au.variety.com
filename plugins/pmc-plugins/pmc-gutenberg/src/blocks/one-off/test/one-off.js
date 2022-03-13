import { oneOff, formatSelectOptions } from '../one-off.js';

describe( 'One Off block tests', () => {
	it( 'returns null for the save method', () => {
		expect( oneOff.save() ).toBe( null );
	} );

	it( 'formats the select options', () => {
		const options = formatSelectOptions( [
			{
				name: 'Test 1',
				slug: 'test-1',
			},
		] );

		expect( options[ 0 ] ).toHaveProperty( 'value' );
	} );

	it( 'returns empty if no options are present', () => {
		const options = formatSelectOptions( [] );

		expect( options.length ).toBe( 0 );
	} );
} );
