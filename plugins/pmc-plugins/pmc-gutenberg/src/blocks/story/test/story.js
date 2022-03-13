import { storyBlock } from '../story.js';

describe( 'Story block tests', () => {
	it( 'returns null for the save method', () => {
		expect( storyBlock.save() ).toBe( null );
	} );
} );
