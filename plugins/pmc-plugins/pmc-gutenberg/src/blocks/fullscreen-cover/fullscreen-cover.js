/**
 * WordPress dependencies.
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { cover as icon } from '@wordpress/icons';

/**
 * Internal dependencies.
 */
import { attributes } from './attributes';
import Edit from './edit';

/**
 * The block object
 */
const fullscreenCover = {
	title: __( 'PMC Fullscreen Cover', 'pmc-gutenberg' ),

	description: __(
		'Render image and CTA to take over full browser screen.',
		'pmc-gutenberg'
	),

	category: 'media',
	icon,

	// This block's `supports` values are set in PHP to allow theme filtering.

	attributes,

	edit: Edit,

	// the save method should always return null
	save: () => null,
};

/**
 * Register the PMC Fullscreen Cover block.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/
 */
registerBlockType( 'pmc/fullscreen-cover', fullscreenCover );

// export for testing
export { fullscreenCover, Edit };
