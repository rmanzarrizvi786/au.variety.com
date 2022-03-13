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
	SelectControl,
} from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment } from '@wordpress/element';
import { addFilter, removeFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

const blockName = 'core/paragraph';
const filterTag = 'blocks.registerBlockType';
const filterNamespace = 'pmc-gutenberg/builtin/paragraph';

/**
 * Add Larva-based typography panel.
 */
const extendEdit = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const {
			attributes: { typographyFontSize },
			setAttributes,
		} = props;

		const fontSizeOptions = [
			{ value: null, label: __( 'Medium (default)', 'pmc-gutenberg' ) },
			{ value: 'body-s', label: __( 'Small', 'pmc-gutenberg' ) },
		];

		const onFontSizeChange = ( value ) => {
			setAttributes( { typographyFontSize: value } );
		};

		return (
			<Fragment>
				<BlockEdit { ...props } />

				<InspectorControls>
					<Panel>
						<PanelBody
							title={ __( 'Typography', 'pmc-gutenberg' ) }
							initialOpen={ false }
						>
							<PanelRow>
								<SelectControl
									label={ __( 'Font Size', 'pmc-gutenberg' ) }
									value={ typographyFontSize }
									options={ fontSizeOptions }
									onChange={ onFontSizeChange }
								/>
							</PanelRow>
						</PanelBody>
					</Panel>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'extendEdit' );

/**
 * Modify paragraph block's options.
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
			typographyFontSize: {
				type: 'string',
				default: null,
			},
		},
		edit: extendEdit( settings.edit ),
	} );
};

addFilter( filterTag, filterNamespace, modifyBlock );
