/**
 * External dependencies.
 */
import { merge } from 'lodash';

/**
 * WordPress dependencies.
 */
import { InspectorControls } from '@wordpress/block-editor';
import {
	Panel,
	PanelBody,
	PanelRow,
	ToggleControl,
} from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment } from '@wordpress/element';
import { addFilter, removeFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

const blockName = 'core/group';
const filterTag = 'blocks.registerBlockType';
const filterNamespace = 'pmc-gutenberg/builtin/group';

/**
 * Add additional controls in sidebar area.
 */
const extendEdit = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const {
			attributes: { backgroundColor, fullBleedBackgroundColor },
			setAttributes,
		} = props;

		return (
			<Fragment>
				<BlockEdit { ...props } />

				<InspectorControls>
					<Panel>
						<PanelBody
							title={ __( 'Display options', 'pmc-gutenberg' ) }
							initialOpen={ false }
						>
							<PanelRow>
								{ backgroundColor && (
									<ToggleControl
										label={ __(
											'Extend background color to edges of screen.',
											'pmc-gutenberg'
										) }
										help={ __(
											'Applies only if a background color is chosen.',
											'pmc-gutengerg'
										) }
										checked={ fullBleedBackgroundColor }
										onChange={ ( value ) => {
											setAttributes( {
												fullBleedBackgroundColor: value,
											} );
										} }
									/>
								) }
							</PanelRow>
						</PanelBody>
					</Panel>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'extendEdit' );

/**
 * Modify group block's options.
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
			fullBleedBackgroundColor: {
				type: 'boolean',
				default: false,
			},
		},
		supports: {
			color: { text: false },
		},
		edit: extendEdit( settings.edit ),
	} );
};

addFilter( filterTag, filterNamespace, overrideSettings );
