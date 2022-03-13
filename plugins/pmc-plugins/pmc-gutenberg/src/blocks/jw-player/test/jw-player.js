import { jwPlayer } from '../jw-player.js';

describe( 'PMC JW Player block tests', () => {
	it( 'returns null for the save method', () => {
		expect( jwPlayer.save() ).toBe( null );
	} );
} );
