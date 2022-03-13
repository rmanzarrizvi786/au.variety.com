/**
 * External dependencies
 */
import { merge } from 'lodash';

/**
 * Internal dependencies
 */
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Block dependencies
 */
import { attributes } from './attributes';
import { Edit } from './edit';

/**
 * Get options for post type select from the config object.
 *
 * @param {Object} blockConfig The block configuration added via localized data on window.
 * @return {Array} A list of objects with value/label for each post type in the config.
 */
const getPostTypeSelectOptions = ( blockConfig ) => {
	const { getPostTypes } = select( 'core' );

	// Method won't return all unless `per_page` is specified.
	// https://github.com/WordPress/gutenberg/issues/15413
	const postTypes = getPostTypes( { per_page: -1 } );

	return Object.keys( blockConfig ).map( ( key ) => {
		const postTypeString = blockConfig[ key ].postType;

		const postTypeObject = postTypes?.filter(
			( type ) => postTypeString === type.slug
		);

		return {
			value: postTypeString,
			label:
				'undefined' === typeof postTypeObject ||
				1 !== postTypeObject.length
					? postTypeString
					: postTypeObject[ 0 ].labels.name,
		};
	} );
};

/**
 * Generate a block registration object for story blocks with
 * default attributes based on configuration.
 *
 * @param {Object} overrides An optional object containing values to
 *                           merge with those in the settings object.
 * @return {Object} The block registration object.
 */
const getStoryBlockRegistrationObject = ( overrides = {} ) => {
	return merge(
		{},
		{
			// Info for the block inserter
			title: __( 'PMC Story', 'pmc-gutenberg' ),
			description: __(
				'Show a post summary and a link',
				'pmc-gutenberg'
			),
			category: 'embed',
			icon: 'format-aside',
			supports: {
				anchor: false,
				customClassName: false,
				html: false,
				pmc: {
					colors: {
						background: false,
					},
					contentOverride: false,
					fullWidthImage: false,
				},
			},

			// Consider keywords for searchability
			// see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#keywords-optional

			attributes,
			edit: Edit,
			save: () => null,
		},
		overrides
	);
};

export { getPostTypeSelectOptions, getStoryBlockRegistrationObject };
