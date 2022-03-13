/**
 * WordPress dependencies.
 */
import { InnerBlocks, RichText, useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * pmc-gutenberg dependencies.
 */
import { default as svgEdit } from '../svg/edit';

const Edit = ( {
	attributes: { svgSlug, heading, text },
	isSelected,
	setAttributes,
} ) => {
	const classNameGridItem = 'lrv-a-grid-item';
	const blockProps = useBlockProps();

	let richTextClassNames = 'lrv-u-margin-t-150';
	if ( isSelected ) {
		richTextClassNames += ' lrv-u-border-a-1 lrv-u-border-color-grey-light';
	}

	return (
		<div className="lrv-a-grid lrv-a-cols lrv-a-cols2@desktop lrv-a-cols2@tablet">
			<div className={ classNameGridItem }>
				<div className="lrv-a-grid lrv-a-cols lrv-a-cols3@desktop lrv-a-cols3@tablet lrv-u-align-items-center">
					<div className={ classNameGridItem }>
						{ svgEdit( {
							name: 'pmc/svg',
							attributes: { slug: svgSlug },
							setAttributes: ( { slug } ) => {
								setAttributes( { svgSlug: slug } );
							},
						} ) }
					</div>
					<div className={ `${ classNameGridItem } lrv-a-span2` }>
						{ ! isSelected && <h3>{ heading }</h3> }

						{ isSelected && (
							<TextControl
								value={ heading }
								placeholder={ __(
									'Enter title',
									'pmc-gutenberg'
								) }
								label={ __( 'Enter title', 'pmc-gutenberg' ) }
								hideLabelFromVision
								onChange={ ( value ) => {
									setAttributes( { heading: value } );
								} }
							/>
						) }
					</div>
				</div>

				<RichText
					value={ text }
					placeholder={ __( 'Enter review', 'pmc-gutenberg' ) }
					onChange={ ( value ) => {
						setAttributes( { text: value } );
					} }
					tagName="div"
					multiline="p"
					className={ richTextClassNames }
				/>
			</div>

			<div className={ classNameGridItem } { ...blockProps }>
				<InnerBlocks
					allowedBlocks={ [ 'core/gallery' ] }
					template={ [ [ 'core/gallery', {} ] ] }
					templateLock="all"
					renderAppender={ false }
				/>
			</div>
		</div>
	);
};

export default Edit;
