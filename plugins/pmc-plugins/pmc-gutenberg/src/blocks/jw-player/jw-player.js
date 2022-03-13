import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import { attributes } from './attributes';
import { Edit } from './edit';

/**
 * The block object
 */
const jwPlayer = {
	/**
	 * This is the display title for your block, which can be translated with `i18n` functions.
	 * The block inserter will show this name.
	 */
	title: __( 'PMC JW Player', 'pmc-gutenberg' ),

	/**
	 * This is a short description for your block, can be translated with `i18n` functions.
	 * It will be shown in the Block Tab in the Settings Sidebar.
	 */
	description: __(
		'Embed a video or playlist from JW Player',
		'pmc-gutenberg'
	),

	category: 'embed',

	/**
	 * Can either be a dashicon from https://developer.wordpress.org/resource/dashicons/
	 * or an svg element.
	 *
	 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#icon-optional
	 */
	icon: 'format-video',

	supports: {
		anchor: false,
		customClassName: false,
		html: false,
	},

	attributes,

	keywords: [
		__( 'embed', 'pmc-gutenberg' ),
		__( 'jwplayer', 'pmc-gutenberg' ),
		__( 'playlist', 'pmc-gutenberg' ),
		__( 'video', 'pmc-gutenberg' ),
	],

	// for readability, this is kept external
	// for complex blocks, consider an edit.js file
	edit: Edit,

	// the save method should always return null
	save: () => null,
};

/**
 * Register the PMC JW Player block.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/
 */
registerBlockType( 'pmc/jw-player', jwPlayer );

// export for testing
export { jwPlayer, Edit };
