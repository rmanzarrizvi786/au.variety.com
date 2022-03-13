/**
 * WordPress dependencies.
 */
import { BlockControls, InspectorControls } from '@wordpress/block-editor';
import { hasBlockSupport } from '@wordpress/blocks';
import {
	Panel,
	PanelBody,
	PanelRow,
	ToggleControl,
	ToolbarButton,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import ImageSelect from './image-select';
import ImagePreview from './image-preview';
import ItemSelects from './item-select';

const coverAdMetaKey = '_pmc_digital_daily_issue_has_cover_ad';

const Edit = ( {
	attributes: { imageId, items },
	name,
	setAttributes,
	isSelected,
} ) => {
	const postType = useSelect(
		( select ) => select( 'core/editor' ).getCurrentPostType(),
		[]
	);
	const storyOptions = useSelect(
		( select ) => select( 'core/editor' ).getCurrentPost().blockData[ name ]
	);

	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );
	const supportsCoverAd = hasBlockSupport(
		name,
		'pmc.coverAdOverlay',
		false
	);

	const classNameGridWrapper =
		'lrv-a-grid lrv-a-cols lrv-a-cols2@desktop lrv-a-cols2@tablet lrv-u-align-items-center';
	const classNameGridItem = 'lrv-a-grid-item';

	const itemSelectsProps = {
		items,
		storyOptions,
		setAttributes,
		isSelected,
		classNameGridWrapper,
		classNameGridItem,
	};

	return (
		<>
			<div className={ classNameGridWrapper }>
				<div className={ classNameGridItem }>
					<h3>{ __( 'In This Issue', 'pmc-gutenberg' ) }</h3>

					<ul
						className={ 'lrv-u-padding-l-050' }
						style={ {
							listStyleType: 'none',
							paddingLeft: '0.5rem',
							marginTop: 0,
							marginBottom: 0,
						} }
					>
						<ItemSelects props={ itemSelectsProps } />
					</ul>
				</div>

				<div className={ classNameGridItem }>
					{ imageId ? (
						<ImagePreview imageId={ imageId } />
					) : (
						<ImageSelect setAttributes={ setAttributes } />
					) }
				</div>
			</div>

			<BlockControls>
				<ToolbarButton
					label={ __( 'Replace Image', 'pmc-gutenberg' ) }
					onClick={ () => {
						setAttributes( { imageId: '' } );
					} }
				>
					{ __( 'Replace Image', 'pmc-gutenberg' ) }
				</ToolbarButton>
			</BlockControls>

			<InspectorControls>
				<Panel>
					<PanelBody
						title={ __( 'Display options', 'pmc-gutenberg' ) }
						initialOpen={ false }
					>
						{ supportsCoverAd && (
							<PanelRow>
								<ToggleControl
									label={ __(
										'Issue has cover ad',
										'pmc-gutenberg'
									) }
									help={
										meta[ coverAdMetaKey ]
											? __(
													'An advertisement will overlay the cover image when the landing page first loads.',
													'pmc-gutengerg'
											  )
											: __(
													'No advertisement is expected to overlay the cover image when the landing page first loads.',
													'pmc-gutengerg'
											  )
									}
									checked={ meta[ coverAdMetaKey ] }
									onChange={ ( value ) => {
										setMeta( {
											...meta,
											[ coverAdMetaKey ]: value,
										} );
									} }
								/>
							</PanelRow>
						) }
					</PanelBody>
				</Panel>
			</InspectorControls>
		</>
	);
};

export default Edit;
