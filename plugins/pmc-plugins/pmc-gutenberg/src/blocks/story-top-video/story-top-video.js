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

const blockName = 'pmc/story-top-video';
const config = window[ getBlockConfigKey( blockName ) ];

/**
 * Configure story block for Top Video embeds.
 */
const storyBlock = getStoryBlockRegistrationObject( {
	title: __( 'PMC Top Video', 'pmc-gutenberg' ),
	description: __( 'Embed a video from PMC Top Videos.', 'pmc-gutenberg' ),
	icon: 'format-video',
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
