import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import { attributes } from './attributes';
import Edit from './edit';
import Save from './save';

/**
 * The block object
 */
const groupSlider = {
	title: __( 'PMC Slider', 'pmc-gutenberg' ),
	description: __(
		"Create slider of Group blocks's inner blocks.",
		'pmc-gutenberg'
	),

	category: 'design',
	icon: 'leftright',
	keywords: [ 'slider', 'carousel', 'group' ],

	attributes,
	supports: {
		// Removes input field for HTML anchor.
		anchor: false,
		// Removes input field for extra CSS classes.
		customClassName: false,
		// Removes support for an HTML mode.
		html: false,
	},

	edit: Edit,
	save: Save,
};

/**
 * Register the PMC Slider block.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/
 */
registerBlockType( 'pmc/group-slider', groupSlider );

// export for testing
export { groupSlider, Edit };
