import { getBlockConfig, getBlockConfigKey } from '../config';

describe( 'get block configuration key', () => {
	it( 'returns a formatted key for retrieving config from the window', () => {
		expect( getBlockConfigKey( 'pmc/story' ) ).toEqual(
			'pmc_story_block_config'
		);
	} );

	it( 'gets a a mutli word block name', () => {
		expect( getBlockConfigKey( 'pmc/story-influencers' ) ).toEqual(
			'pmc_story_influencers_block_config'
		);
	} );
} );

describe( 'get block configuration', () => {
	const windowSpy = jest.spyOn( window, 'window', 'get' );

	it( 'returns undefined when block config is not available', () => {
		windowSpy.mockImplementation( () => ( {
			pmc_gutenberg_blocks_config: {},
		} ) );

		expect( getBlockConfig( 'pmc/carousel' ) ).toBeUndefined();
	} );
	windowSpy.mockClear();

	it( 'returns a block configuration from the window', () => {
		windowSpy.mockImplementation( () => ( {
			pmc_gutenberg_blocks_config: {
				pmc_carousel_block_config: {},
			},
		} ) );
		expect( getBlockConfig( 'pmc/carousel' ) ).toEqual( {} );
	} );

	windowSpy.mockClear();
} );
