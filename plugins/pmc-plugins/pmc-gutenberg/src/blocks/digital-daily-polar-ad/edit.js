/**
 * WordPress dependencies.
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * The edit function for digitalDailyPolarAd
 *
 */
const Edit = () => {
	const blockProps = useBlockProps();

	return (
		<div className="lrv-a-grid lrv-a-cols lrv-a-cols2@desktop lrv-a-cols2@tablet">
			<div className="lrv-a-grid-item" { ...blockProps }>
				<InnerBlocks
					allowedBlocks={ [ 
						'pmc/ad',
						'pmc/story-digital-daily'
] }
					template={ [
						[ 'pmc/ad', {} ],
						[ 'pmc/story-digital-daily', {} ],
] }
					templateLock="all"
					renderAppender={ false }
				/>
			</div>
		</div>
	);
};

export default Edit;
