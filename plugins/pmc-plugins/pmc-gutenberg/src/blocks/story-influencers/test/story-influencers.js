import { influencersStoryBlock } from '../story-influencers.js';

describe( 'Influencers Story Card block tests', () => {
	it( 'returns null for the save method', () => {
		expect( influencersStoryBlock.save() ).toBe( null );
	} );

	it( 'has a default post type of influencers', () => {
		expect( influencersStoryBlock.attributes.postType.default ).toBe(
			'influencers'
		);
	} );
} );
