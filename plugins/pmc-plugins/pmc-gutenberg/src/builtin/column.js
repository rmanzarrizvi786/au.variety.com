/**
 * External dependencies.
 */
import { merge } from 'lodash';

/**
 * WordPress dependencies.
 */
import { addFilter, removeFilter } from '@wordpress/hooks';

const blockName = 'core/column';
const filterTag = 'blocks.registerBlockType';
const filterNamespace = 'pmc-gutenberg/builtin/column';

/**
 * Remove unnecessary settings from block.
 *
 * @param {Object} settings Block type settings array.
 * @param {string} name     Name of block type.
 * @return {Object} Array of merged block variant names and keywords.
 */
const overrideSettings = ( settings, name ) => {
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
				default: 'lrv-a-grid-item',
			},
		},
		supports: {
			color: { background: false, text: false },
		},
	} );
};

addFilter( filterTag, filterNamespace, overrideSettings );
