/**
 * WordPress dependencies.
 */
import {
	InnerBlocks,
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Panel,
	PanelBody,
	PanelRow,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const Edit = ( {
	attributes: { heading, subheading, showBrandLogo },
	clientId,
	isSelected,
	setAttributes,
} ) => {
	const isInnerBlockSelected = useSelect( ( select ) =>
		select( 'core/block-editor' ).hasSelectedInnerBlock( clientId, true )
	);
	const showTextInputs = isSelected || isInnerBlockSelected;

	const classNameGridItem = 'lrv-a-grid-item';
	const blockProps = useBlockProps();

	const titleInputs = [
		{
			attribute: 'heading',
			label: __( 'Enter title', 'pmc-gutenberg' ),
			value: heading,
		},
		{
			attribute: 'subheading',
			label: __( 'Enter subtitle', 'pmc-gutenberg' ),
			value: subheading,
		},
	];

	return (
		<>
			<div className="lrv-u-border-a-1 lrv-u-border-color-grey-light lrv-u-padding-a-025">
				<div className="lrv-a-grid lrv-a-cols lrv-a-cols2@desktop lrv-a-cols2@tablet">
					{ titleInputs.map( ( { attribute, label, value } ) => (
						<div className={ classNameGridItem } key={ attribute }>
							{ ! showTextInputs && <h3>{ value }</h3> }

							{ showTextInputs && (
								<TextControl
									value={ value }
									placeholder={ label }
									label={ label }
									hideLabelFromVision
									onChange={ ( newValue ) => {
										setAttributes( {
											[ attribute ]: newValue,
										} );
									} }
								/>
							) }
						</div>
					) ) }
				</div>

				<div className="lrv-u-margin-t-150" { ...blockProps }>
					<InnerBlocks allowedBlocks={ [ 'core/group' ] } />
				</div>
			</div>

			<InspectorControls>
				<Panel>
					<PanelBody
						title={ __( 'Display Options', 'pmc-gutenberg' ) }
						initialOpen={ true }
					>
						<PanelRow>
							<ToggleControl
								label={ __(
									'Show brand logo?',
									'pmc-gutenberg'
								) }
								help={ __(
									"If checked, the brand's logo will appear to the left of the title.",
									'pmc-gutenberg'
								) }
								checked={ showBrandLogo }
								onChange={ ( value ) => {
									setAttributes( { showBrandLogo: value } );
								} }
							/>
						</PanelRow>
					</PanelBody>
				</Panel>
			</InspectorControls>
		</>
	);
};

export default Edit;
