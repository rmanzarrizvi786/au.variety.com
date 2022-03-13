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

const blockName = 'pmc/story-gallery';
const config = window[ getBlockConfigKey( blockName ) ];

/**
 * Configure story block for Gallery embeds.
 */
const storyBlock = getStoryBlockRegistrationObject( {
	title: __( 'PMC Gallery', 'pmc-gutenberg' ),
	description: __( 'Embed a PMC Gallery.', 'pmc-gutenberg' ),
	icon: 'format-gallery',
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
