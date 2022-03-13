import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { image as icon } from '@wordpress/icons';

import { attributes } from './attributes';
import Edit from './edit';

/**
 * The block object
 */
const svg = {
	/**
	 * This is the display title for your block, which can be translated with `i18n` functions.
	 * The block inserter will show this name.
	 */
	title: __( 'PMC SVG', 'pmc-gutenberg' ),

	/**
	 * This is a short description for your block, can be translated with `i18n` functions.
	 * It will be shown in the Block Tab in the Settings Sidebar.
	 */
	description: __( 'Insert an SVG.', 'pmc-gutenberg' ),

	category: 'media',

	/**
	 * Can either be a dashicon from https://developer.wordpress.org/resource/dashicons/
	 * or an svg element.
	 *
	 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#icon-optional
	 */
	icon,

	supports: {
		anchor: false,
		customClassName: false,
		html: false,
	},

	attributes,

	edit: Edit,

	// the save method should always return null
	save: () => null,
};

/**
 * Register the PMC SVG block.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/
 */
registerBlockType( 'pmc/svg', svg );

// export for testing
export { svg, Edit };
