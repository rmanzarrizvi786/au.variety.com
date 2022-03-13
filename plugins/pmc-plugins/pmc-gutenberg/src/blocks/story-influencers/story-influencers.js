import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import { getStoryBlockRegistrationObject } from '../story/utils';

/**
 * Extending the story block w/o theme configuration.
 * This will be removed once the existing influencer
 * blocks are transformed to story blocks.
 */

const influencerOverrides = {
	title: __( 'PMC Influencer Story Card', 'pmc-gutenberg' ),
	icon: 'admin-users',
	attributes: {
		postType: {
			default: 'influencers',
		},
	},
};

const influencersStoryBlock = getStoryBlockRegistrationObject(
	influencerOverrides
);

registerBlockType( 'pmc/story-influencers', influencersStoryBlock );

export { influencersStoryBlock };
