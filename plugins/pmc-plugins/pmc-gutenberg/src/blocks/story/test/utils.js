import { getPostTypeSelectOptions } from '../utils.js';

jest.mock( '@wordpress/block-editor', () => {
	return {
		withColors: () => jest.fn(),
	};
} );
jest.mock( '@wordpress/components', () => jest.fn() );
jest.mock( '@wordpress/data', () => {
	return {
		combineReducers: () => jest.fn(),
		createReduxStore: () => jest.fn(),
		select: () => {
			return {
				getPostTypes: () => [
					{
						slug: 'test',
						labels: {
							name: 'Test',
						},
					},
					{
						slug: 'second-test',
						labels: {
							name: 'Second Test',
						},
					},
				],
			};
		},
		register: () => jest.fn(),
	};
} );

describe( 'Story block utils', () => {
	it( 'returns a list of select options for post type', () => {
		const config = {
			test: {
				postType: 'test',
				taxonomySlug: 'test-slug',
				viewMoreText: 'View Test',
			},
			'second-test': {
				postType: 'second-test',
				isDefault: true,
				taxonomySlug: 'second-test-slug',
				viewMoreText: 'View Second Test',
			},
		};
		const labels = {
			test: 'Test',
			'second-test': 'Second Test',
		};

		const result = getPostTypeSelectOptions( config );

		result.forEach( ( item, i ) => {
			expect( item ).toHaveProperty(
				'value',
				config[ Object.keys( config )[ i ] ].postType
			);
			expect( item ).toHaveProperty(
				'label',
				labels[ Object.keys( config )[ i ] ]
			);
		} );
	} );
} );
