/**
 * External dependencies.
 */
import { merge } from 'lodash';

/**
 * WordPress dependencies.
 */
import { registerBlockStyle, unregisterBlockStyle } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';
import { addFilter, removeFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

const blockName = 'core/separator';
const filterTag = 'blocks.registerBlockType';
const filterNamespace = 'pmc-gutenberg/builtin/columns';

/**
 * Modify separator styles.
 *
 * https://developer.wordpress.org/block-editor/developers/filters/block-filters/#block-style-variations
 */
function setup() {
	unregisterBlockStyle( blockName, 'dots' );

	registerBlockStyle( blockName, {
		name: 'thick',
		label: __( 'Thick Line', 'pmc-gutenberg' ),
	} );

	registerBlockStyle( blockName, {
		name: 'wide-thick',
		label: __( 'Wide, Thick Line', 'pmc-gutenberg' ),
	} );
}

/**
 * Run setup on domReady.
 * https://github.com/WordPress/gutenberg/pull/11532#issuecomment-442412409
 */
domReady( setup );

/**
 * Modify block's options.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Block settings.
 */
const modifyBlock = ( settings, name ) => {
	if ( blockName !== name ) {
		return settings;
	}

	// Do not modify block's deprecations.
	// Needed until https://github.com/WordPress/gutenberg/pull/36628 is merged.
	removeFilter( filterTag, filterNamespace );

	return merge( {}, settings, {
		attributes: {
			className: {
				type: 'string',
				default: '',
			},
		},
	} );
};

addFilter( filterTag, filterNamespace, modifyBlock );
