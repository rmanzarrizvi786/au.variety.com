/**
 * External dependencies.
 */
import { merge } from 'lodash';

/**
 * WordPress dependencies.
 */
import { addFilter, removeFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

const blockName = 'core/gallery';
const filterTag = 'blocks.registerBlockType';
const filterNamespace = 'pmc-gutenberg/builtin/gallery';

/**
 * Modify block's settings.
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
		title: __( 'Inline Gallery', 'pmc-gutenberg' ),
	} );
};

addFilter( filterTag, filterNamespace, modifyBlock );
