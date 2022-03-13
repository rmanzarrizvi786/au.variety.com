/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { cover as icon } from '@wordpress/icons';

/**
 * PMC Gutenberg dependencies
 */
import { getBlockConfigKey } from '../helpers/config';

/**
 * Block dependencies
 */
import { getStoryBlockRegistrationObject } from '../story/utils';

const blockName = 'pmc/story-digital-daily-special-edition-cover';
const config = window[ getBlockConfigKey( blockName ) ];

/**
 * Configure Digital Daily story block.
 */
const storyBlock = getStoryBlockRegistrationObject( {
	title: __( 'PMC Digital Daily Special Edition Cover', 'pmc-gutenberg' ),
	attributes: {
		postType: {
			default: Object.keys( config )[ 0 ],
		},
	},
	category: 'media',
	description: __(
		'Display cover for Special Edition article',
		'pmc-gutenberg'
	),
	icon,
	supports: {
		multiple: false,
		pmc: { contentOverride: true },
	},
} );

registerBlockType( blockName, storyBlock );

export { storyBlock };
