/**
 * WordPress dependencies.
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Block dependencies.
 */
import './document-settings';
import './inspector-control-cropping';
import { getStoryBlockRegistrationObject } from '../story/utils';

const blockName = 'pmc/story-digital-daily';

/**
 * Configure Digital Daily story block.
 */
const storyBlock = getStoryBlockRegistrationObject( {
	title: __( 'PMC Digital Daily Story', 'pmc-gutenberg' ),
	description: __(
		'Embed an article in a Digital Daily issue.',
		'pmc-gutenberg'
	),
	attributes: {
		imageCropClass: {
			type: 'string',
			default: null,
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
