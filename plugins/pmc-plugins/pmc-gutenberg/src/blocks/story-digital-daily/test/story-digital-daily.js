import { storyBlock } from '../story-digital-daily';

describe( 'PMC Story Gallery block tests', () => {
	it( 'returns null for the save method', () => {
		expect( storyBlock.save() ).toBe( null );
	} );
} );
