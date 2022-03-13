/**
 * WordPress dependencies.
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import Edit from './edit';
import Save from './save';

const digitalDailyPolarAd = {
	title: __( 'PMC Digital Daily Polar Ad', 'pmc-gutenberg' ),

	description: __( 'Show a Polar advertisement in landing-page view and a Digital Daily story block in full view', 'pmc-gutenberg' ),

	category: 'embed',
	icon: 'format-aside',
	keywords: [ 'ad', 'polar', 'story' ],

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

registerBlockType( 'pmc/digital-daily-polar-ad', digitalDailyPolarAd );

export { digitalDailyPolarAd, Edit };
