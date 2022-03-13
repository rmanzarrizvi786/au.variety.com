/**
 * External dependencies
 */
import {
	AlignmentToolbar,
	BlockControls,
	InspectorControls,
	MediaReplaceFlow,
	PanelColorSettings,
	withColors,
} from '@wordpress/block-editor';
import { hasBlockSupport } from '@wordpress/blocks';
import {
	Panel,
	PanelBody,
	PanelRow,
	ToggleControl,
	ToolbarButton,
	withFilters,
} from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { alignCenter, alignLeft, alignRight } from '@wordpress/icons';

/**
 * PMC Gutenberg dependencies
 */
import { getBlockConfigKey } from '../helpers/config';

/**
 * Block dependencies
 */
import { attributes as defaultAttributes } from './attributes';
import { getPostTypeSelectOptions } from './utils';
import { SetupState } from './setup-state';
import { EditState } from './edit-state';

/**
 * The edit function for StoryCard
 *
 * @param {Object} props
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 */
const Edit = ( props ) => {
	// https://developer.wordpress.org/block-editor/tutorials/block-tutorial/block-controls-toolbar-and-sidebar/
	const {
		attributes: {
			postType,
			postID,
			contentOverride,
			hasContentOverride,
			hasDisplayedExcerpt,
			hasDisplayedByline,
			hasDisplayedPrimaryTerm,
			hasFullWidthImage,
			alignment,
			title,
			excerpt,
			featuredImageID,
			viewMoreText,
			className,
		},
		backgroundColor,
		name,
		setAttributes,
		setBackgroundColor,
	} = props;

	const { __unstableMarkNextChangeAsNotPersistent } = useDispatch(
		'core/block-editor'
	);

	// Set up data configured by the theme
	const blockConfig = window[ getBlockConfigKey( name ) ];
	const postTypeOptions = getPostTypeSelectOptions( blockConfig );

	const supportsBackgroundColor = hasBlockSupport(
		name,
		'pmc.colors.background',
		false
	);

	const supportsContentOverride = hasBlockSupport(
		name,
		'pmc.contentOverride',
		false
	);

	const supportsFullWidthImage = hasBlockSupport(
		name,
		'pmc.fullWidthImage',
		false
	);

	const updateConfiguredAttributes = ( type ) => {
		setAttributes( {
			taxonomySlug: blockConfig[ type ].taxonomySlug,
			viewMoreText: blockConfig[ type ].viewMoreText,
		} );
	};

	/**
	 * Update these attributes on load to account for when the `postType` is not
	 * updated.
	 *
	 * Call to `__unstableMarkNextChangeAsNotPersistent()` prevents this update
	 * from blocking a user from undoing story-block insertion. Because
	 * 'updateConfiguredAttributes()' is called automatically each time the Edit
	 * function is invoked, it perpetually creates an undo level any time the
	 * block's state changes, including when the user clicks the undo button to
	 * remove the block.
	 *
	 * @see https://github.com/WordPress/gutenberg/pull/26377
	 */
	__unstableMarkNextChangeAsNotPersistent();
	updateConfiguredAttributes( postType );

	const onChangePostID = ( newValue ) => {
		setAttributes( {
			postID: newValue === undefined ? null : newValue,
		} );
	};

	const onChangeHasDisplayedExcerpt = ( newValue ) => {
		setAttributes( {
			hasDisplayedExcerpt: newValue === undefined ? true : newValue,
		} );
	};

	const onChangeHasDisplayedByline = ( newValue ) => {
		setAttributes( {
			hasDisplayedByline: newValue === undefined ? true : newValue,
		} );
	};

	const onChangeHasDisplayedPrimaryTerm = ( newValue ) => {
		setAttributes( {
			hasDisplayedPrimaryTerm: newValue === undefined ? true : newValue,
		} );
	};

	const onChangeHasFullWidthImage = ( newValue ) => {
		setAttributes( {
			hasFullWidthImage: newValue === undefined ? false : newValue,
		} );
	};

	const onChangeAlignment = ( newValue ) => {
		setAttributes( {
			alignment: newValue === undefined ? 'none' : newValue,
		} );
	};

	const onChangeTitle = ( newValue ) => {
		setAttributes( {
			title: newValue === undefined ? null : newValue,
		} );
	};

	const onChangeExcerpt = ( newValue ) => {
		setAttributes( {
			excerpt: newValue === undefined ? null : newValue,
		} );
	};

	const onChangeFeaturedImageID = ( image ) => {
		setAttributes( {
			featuredImageID: image === undefined ? null : image.id,
		} );
	};

	const onChangePostType = ( newPostType ) => {
		setAttributes( {
			postType: newPostType,
		} );

		// Update the configured values based on the newly selected post type.
		updateConfiguredAttributes( newPostType );
	};

	const onChangeHasContentOverride = ( newValue ) => {
		setAttributes( { hasContentOverride: newValue } );
	};

	const onContentOverrideUpdate = ( value ) => {
		setAttributes( { contentOverride: value } );
	};

	const ALIGNMENT_CONTROLS = [
		{
			icon: alignLeft,
			title: __( 'Align left', 'pmc-gutenberg' ),
			align: 'left',
		},
	];

	if (
		null === className ||
		( 'undefined' !== typeof className &&
			-1 !== className.indexOf( 'horizontal' ) )
	) {
		ALIGNMENT_CONTROLS.push( {
			icon: alignRight,
			title: __( 'Align right', 'pmc-gutenberg' ),
			align: 'right',
		} );

		if ( 'center' === alignment ) {
			onChangeAlignment( 'right' );
		}
	}
	if (
		'undefined' !== typeof className &&
		-1 !== className.indexOf( 'vertical' )
	) {
		ALIGNMENT_CONTROLS.push( {
			icon: alignCenter,
			title: __( 'Align center', 'pmc-gutenberg' ),
			align: 'center',
		} );

		if ( 'right' === alignment ) {
			onChangeAlignment( 'center' );
		}
	}

	const AdditionalDisplayControls = withFilters(
		'pmcGutenberg.storyBlock.additionalDisplayControls'
		// eslint-disable-next-line no-unused-vars
	)( ( blockProps ) => <></> );

	return (
		<>
			{ /* Block Toolbar */ }
			{ postID && (
				<BlockControls>
					<AlignmentToolbar
						value={ alignment }
						onChange={ onChangeAlignment }
						alignmentControls={ ALIGNMENT_CONTROLS }
					/>
					<ToolbarButton
						label={ __( 'Replace', 'pmc-gutenberg' ) }
						onClick={ () => {
							const resetAttributes = {};

							// Reformat block's attributes definition into props.
							for ( const [ key, value ] of Object.entries(
								defaultAttributes
							) ) {
								resetAttributes[ key ] = value.default;
							}

							resetAttributes.postType = postType;
							resetAttributes.taxonomySlug =
								blockConfig[ postType ].taxonomySlug;
							resetAttributes.viewMoreText =
								blockConfig[ postType ].viewMoreText;

							setAttributes( resetAttributes );
						} }
					>
						{ __( 'Replace', 'pmc-gutenberg' ) }
					</ToolbarButton>
					<MediaReplaceFlow
						allowedTypes={ [ 'image' ] }
						accept="image/*"
						onSelect={ onChangeFeaturedImageID }
						name={ __( 'Override Image', 'pmc-gutenberg' ) }
					/>
				</BlockControls>
			) }

			{ /* Block Content */ }

			{ postID ? (
				<EditState
					postType={ postType }
					postID={ postID }
					contentOverride={ contentOverride }
					hasContentOverride={ hasContentOverride }
					hasDisplayedExcerpt={ hasDisplayedExcerpt }
					hasFullWidthImage={ hasFullWidthImage }
					alignment={ alignment }
					title={ title }
					excerpt={ excerpt }
					featuredImageID={ featuredImageID }
					onChangeTitle={ onChangeTitle }
					onChangeExcerpt={ onChangeExcerpt }
					onContentOverrideUpdate={ onContentOverrideUpdate }
					viewMoreText={ viewMoreText }
				/>
			) : (
				<SetupState
					postType={ postType }
					onChangePostID={ onChangePostID }
					onChangePostType={ onChangePostType }
					placeholderTitle={ __( 'Select a Story', 'pmc-gutenberg' ) }
					postTypeSelectOptions={ postTypeOptions }
				/>
			) }

			{ /* Settings Sidebar */ }
			<InspectorControls>
				<Panel>
					<PanelBody
						title={ __( 'Display Settings', 'pmc-gutenberg' ) }
						initialOpen={ true }
					>
						<PanelRow>
							<ToggleControl
								label={ __( 'Display dek?', 'pmc-gutenberg' ) }
								help={
									hasDisplayedExcerpt
										? __(
												'Dek will be shown (if design includes it).',
												'pmc-gutenberg'
										  )
										: __(
												'Dek will be hidden (if design includes it).',
												'pmc-gutenberg'
										  )
								}
								checked={ hasDisplayedExcerpt }
								onChange={ onChangeHasDisplayedExcerpt }
							/>
						</PanelRow>

						<PanelRow>
							<ToggleControl
								label={ __(
									'Display byline?',
									'pmc-gutenberg'
								) }
								help={
									hasDisplayedByline
										? __(
												'Byline will be shown (if design includes it).',
												'pmc-gutenberg'
										  )
										: __(
												'Byline will be hidden (if design includes it).',
												'pmc-gutenberg'
										  )
								}
								checked={ hasDisplayedByline }
								onChange={ onChangeHasDisplayedByline }
							/>
						</PanelRow>

						<PanelRow>
							<ToggleControl
								label={ __(
									'Display breadcrumb?',
									'pmc-gutenberg'
								) }
								help={
									hasDisplayedPrimaryTerm
										? __(
												'Taxonomy term (breadcrumb) will be shown (if design includes it).',
												'pmc-gutenberg'
										  )
										: __(
												'Taxonomy term (breadcrumb) will be hidden (if design includes it).',
												'pmc-gutenberg'
										  )
								}
								checked={ hasDisplayedPrimaryTerm }
								onChange={ onChangeHasDisplayedPrimaryTerm }
							/>
						</PanelRow>

						{ supportsFullWidthImage && (
							<PanelRow>
								<ToggleControl
									label={ __(
										'Make image full-width',
										'pmc-gutenberg'
									) }
									help={
										hasFullWidthImage
											? __(
													'Image fills the width of the story card.',
													'pmc-gutenberg'
											  )
											: __(
													'Image is thumbnail size.',
													'pmc-gutenberg'
											  )
									}
									checked={ hasFullWidthImage }
									onChange={ onChangeHasFullWidthImage }
								/>
							</PanelRow>
						) }

						{ supportsContentOverride && (
							<PanelRow>
								<ToggleControl
									label={ __(
										'Override excerpt?',
										'pmc-gutenberg'
									) }
									help={
										hasContentOverride
											? __(
													'Use customized post excerpt.',
													'pmc-gutenberg'
											  )
											: __(
													'Use automatically-generated excerpt.',
													'pmc-gutenberg'
											  )
									}
									checked={ hasContentOverride }
									onChange={ onChangeHasContentOverride }
								/>
							</PanelRow>
						) }

						<AdditionalDisplayControls { ...props } />
					</PanelBody>

					{ supportsBackgroundColor && (
						<PanelColorSettings
							title={ __( 'Colors', 'pmc-gutenberg' ) }
							colorSettings={ [
								{
									value: backgroundColor.color,
									onChange: setBackgroundColor,
									label: __(
										'Background Color',
										'pmc-gutenberg'
									),
								},
							] }
						/>
					) }
				</Panel>
			</InspectorControls>
		</>
	);
};

/**
 * Inject colorpicker HOC.
 */
const editWithColors = withColors( 'backgroundColor', {
	backgroundColor: 'color',
} )( Edit );

export { editWithColors as Edit };
