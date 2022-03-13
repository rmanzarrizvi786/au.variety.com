import { SelectControl } from '@wordpress/components';
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

const formatSelectOptions = ( themeConfig ) => {
	try {
		return themeConfig.map( ( item ) => {
			return {
				label: item.name,
				value: item.slug,
			};
		} );
	} catch {
		return [];
	}
};

// Get one-off config provided by the theme as localized data.
const config =
	undefined !== window.pmc_theme_one_offs ? window.pmc_theme_one_offs : [];

/**
 * The edit function for oneOff
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 */

const Edit = ( { attributes, setAttributes } ) => {
	const options = formatSelectOptions( config );
	options.unshift( {
		label: 'Please Select a One Off',
		value: '',
	} );

	return (
		<SelectControl
			label={ __( 'Select a One Off template: ', 'pmc-gutenberg' ) }
			value={ attributes.oneOffTemplate }
			onChange={ ( oneOffTemplate ) =>
				setAttributes( { oneOffTemplate } )
			}
			options={ options }
		/>
	);
};

/**
 * The block object
 */
const oneOff = {
	/**
	 * This is the display title for your block, which can be translated with `i18n` functions.
	 * The block inserter will show this name.
	 */
	title: __( 'PMC One Off', 'pmc-gutenberg' ),

	/**
	 * This is a short description for your block, can be translated with `i18n` functions.
	 * It will be shown in the Block Tab in the Settings Sidebar.
	 */
	description: __(
		'A block for handling custom interface provided by the theme.',
		'pmc-gutenberg'
	),

	attributes: {
		oneOffTemplate: {
			type: 'string',
		},
	},

	category: 'design',

	/**
	 * Can either be a dashicon from https://developer.wordpress.org/resource/dashicons/
	 * or an svg element.
	 *
	 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#icon-optional
	 */
	icon: 'screenoptions',

	supports: {
		anchor: false,
		customClassName: false,
		// Removes support for an HTML mode.
		html: false,
	},

	keywords: config.map( ( item ) => item.name ),

	// for readability, this is kept external
	// for complex blocks, consider an edit.js file
	edit: Edit,

	// the save method should always return null
	save: () => null,
};

/**
 * Register the One Off block
 *
 * (if window.pmc_theme_one_offs is defined)
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/
 */

if ( undefined !== window.pmc_theme_one_offs ) {
	registerBlockType( 'pmc/one-off', oneOff );
}

// export for testing
export { oneOff, Edit, formatSelectOptions };
