/**
 * External dependencies.
 */
import { indexOf, mergeWith, pick } from 'lodash';

/**
 * WordPress dependencies.
 */
import { addFilter } from '@wordpress/hooks';

/**
 * Class GlobalOverrides.
 */
class GlobalOverrides {
	/**
	 * Track blocks already modified, to prevent modifying those blocks'
	 * deprecations.
	 *
	 * @see https://github.com/WordPress/gutenberg/pull/36628
	 * @type {Array}
	 */
	blocksSettingsOverridden = [];

	/**
	 * GlobalOverrides constructor.
	 */
	constructor() {
		this.overrideSettings = this.overrideSettings.bind( this );

		// Hooked early so that modifications are present at default priority.
		addFilter(
			'blocks.registerBlockType',
			'pmc-gutenberg/builtin/global-overrides',
			this.overrideSettings,
			9
		);
	}

	/**
	 * Override block settings for all Core blocks.
	 *
	 * @param {Object} settings Block settings.
	 * @param {string} name     Block name.
	 * @return {Object} Modified block settings.
	 */
	overrideSettings( settings, name ) {
		// Apply only to Core blocks.
		if ( 0 !== name.indexOf( 'core/' ) ) {
			return settings;
		}

		// Modify only the initial block registration, but not deprecations.
		// Needed until https://github.com/WordPress/gutenberg/pull/36628 is merged.
		if ( -1 !== indexOf( this.blocksSettingsOverridden, name ) ) {
			return settings;
		}
		this.blocksSettingsOverridden.push( name );

		// Remove unneeded settings without breaking compatibility.
		return mergeWith(
			{},
			settings,
			{
				deprecated: [
					pick( settings, [ 'attributes', 'save', 'supports' ] ),
				],
				supports: {
					anchor: false,
					customClassName: false,
				},
			},
			( objValue, srcValue, key ) => {
				if ( 'deprecated' === key && Array.isArray( objValue ) ) {
					return objValue.concat( srcValue );
				}
			}
		);
	}
}

new GlobalOverrides();
