import { fullscreenCover } from '../fullscreen-cover.js';

describe( 'PMC Fullscreen Cover block tests', () => {
	it( 'returns null for the save method', () => {
		expect( fullscreenCover.save() ).toBe( null );
	} );

	it( 'does not set `supports` arguments in JS, deferring instead to PHP', () => {
		expect( fullscreenCover.supports ).toBe( undefined );
	} );
} );
