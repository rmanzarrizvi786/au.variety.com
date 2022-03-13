/**
 * External dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * PMC Gutenberg dependencies
 */
import { getBlockConfigKey } from '../helpers/config';

/**
 * Block dependencies
 */
import { getStoryBlockRegistrationObject } from '../story/utils';

const blockName = 'pmc/story-runway-review';
const config = window[ getBlockConfigKey( blockName ) ];

/**
 * Configure story block for Runway Review embeds.
 */
const storyBlock = getStoryBlockRegistrationObject( {
	title: __( 'PMC Runway Review', 'pmc-gutenberg' ),
	description: __( 'Embed a Runway Review.', 'pmc-gutenberg' ),
	icon: 'camera',
	attributes: {
		postType: {
			default: Object.keys( config )[ 0 ],
		},
	},
	supports: {
		pmc: {
			colors: {
				background: true,
			},
			contentOverride: true,
		},
	},
} );

registerBlockType( blockName, storyBlock );

export { storyBlock };
