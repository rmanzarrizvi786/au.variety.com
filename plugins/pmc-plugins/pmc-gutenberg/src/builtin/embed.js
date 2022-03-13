import {
	getBlockVariations,
	unregisterBlockVariation,
} from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';
import { addFilter, removeFilter } from '@wordpress/hooks';

const blockName = 'core/embed';
const filterTag = 'blocks.registerBlockType';
const filterNamespace = 'pmc-gutenberg/builtin/embed';

/**
 * Disable all branded embed block variants; keep the generic embed block.
 * https://developer.wordpress.org/block-editor/developers/filters/block-filters/#block-style-variations
 */
function setup() {
	const blockVariantsArr = getBlockVariations( blockName );

	blockVariantsArr.forEach( ( variant ) =>
		unregisterBlockVariation( blockName, variant.name )
	);
}

/**
 * Retain variant names/titles and keywords for search options in embed block.
 *
 * @param {Object} settings - Block type settings array.
 * @param {string} name     - Name of block type.
 * @return {Object} Array of merged block variant names and keywords.
 */
const addEmbedKeywords = ( settings, name ) => {
	if ( blockName !== name ) {
		return settings;
	}

	// Do not modify block's deprecations.
	// Needed until https://github.com/WordPress/gutenberg/pull/36628 is merged.
	removeFilter( filterTag, filterNamespace );

	const embedSettings = {
		...settings,
	};

	const variantsArr = [ ...embedSettings.variations ];

	const variantTitles = variantsArr.map( ( variant ) => variant.title );

	// Filter for variants that have an array of keywords.
	const variantKeywords = variantsArr
		.filter( ( variant ) => variant.keywords )
		.map( ( arr ) => arr.keywords )
		.flat();

	// Remove duplicate keywords.
	const uniqueVariantKeywords = [ ...new Set( variantKeywords ) ];

	embedSettings.keywords = [
		...settings.keywords,
		...variantTitles,
		...uniqueVariantKeywords,
	];

	return embedSettings;
};

addFilter( filterTag, filterNamespace, addEmbedKeywords );

/**
 * Run setup on domReady.
 * https://github.com/WordPress/gutenberg/pull/11532#issuecomment-442412409
 */
domReady( setup );
