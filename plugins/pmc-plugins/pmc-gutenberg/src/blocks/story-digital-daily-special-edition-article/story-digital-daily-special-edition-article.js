/**
 * WordPress dependencies
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
import './document-settings';
import { getStoryBlockRegistrationObject } from '../story/utils';

const blockName = 'pmc/story-digital-daily-special-edition-article';
const config = window[ getBlockConfigKey( blockName ) ];

/**
 * Configure Digital Daily story block.
 */
const storyBlock = getStoryBlockRegistrationObject( {
	title: __( 'PMC Digital Daily Special Edition Story', 'pmc-gutenberg' ),
	attributes: {
		postType: {
			default: Object.keys( config )[ 0 ],
		},
	},
	description: __( 'Embed Special Edition article', 'pmc-gutenberg' ),
} );

registerBlockType( blockName, storyBlock );

export { storyBlock };
