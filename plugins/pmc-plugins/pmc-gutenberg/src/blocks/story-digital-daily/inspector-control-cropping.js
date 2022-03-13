/**
 * External dependencies.
 */
import { PanelRow, SelectControl } from '@wordpress/components';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import { cropOptions } from './crop-options';

const blockName = 'pmc/story-digital-daily';

/**
 * Add to Story Block's Display Settings InspectorControls.
 *
 * @param {Function} Controls Additional inspector controls.
 * @return {Function} Modified inspector controls.
 */
const InspectorControlCropOverride = ( Controls ) => ( props ) => {
	const {
		attributes: { imageCropClass },
		name,
		setAttributes,
	} = props;

	if ( blockName !== name ) {
		return null;
	}

	const onCropUpdate = ( value ) => {
		setAttributes( { imageCropClass: value } );
	};

	return (
		<>
			<Controls { ...props } />

			<PanelRow>
				<SelectControl
					label={ __( 'Override image crop', 'pmc-gutenberg' ) }
					help={ __(
						"Change the crop applied to the story's featured image.",
						'pmc-gutenberg'
					) }
					value={ imageCropClass }
					options={ cropOptions }
					onChange={ onCropUpdate }
				/>
			</PanelRow>
		</>
	);
};

addFilter(
	'pmcGutenberg.storyBlock.additionalDisplayControls',
	blockName,
	InspectorControlCropOverride
);
