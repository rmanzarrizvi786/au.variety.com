/**
 * WordPress dependencies.
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import { attributes } from './attributes';
import Edit from './edit';
import Save from './save';

const inlineGallerySliderWithText = {
	title: __( 'PMC Inline Gallery Slider with Text', 'pmc-gutenberg' ),
	description: __(
		'Create an inline gallery slider accompanied by several paragraphs of text.',
		'pmc-gutenberg'
	),

	category: 'theme',
	icon: 'welcome-widgets-menus',
	keywords: [ 'inline', 'gallery', 'text' ],

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

registerBlockType(
	'pmc/inline-gallery-slider-with-text',
	inlineGallerySliderWithText
);

export { inlineGallerySliderWithText, Edit };
