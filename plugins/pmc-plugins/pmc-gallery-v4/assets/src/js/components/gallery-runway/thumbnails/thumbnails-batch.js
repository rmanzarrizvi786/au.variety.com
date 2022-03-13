import React from 'react';
import Thumbnail from './../../thumbnail';

class ThumbnailsBatch extends React.Component {
	render() {
		const { thumbnails, galleryIndex, isMediumSize, canShow, navigationLocked } = this.props;

		if ( ! canShow ) {
			return null;
		}

		return (
			<ul className="c-gallery-runway-thumbnails__list" >
				{ thumbnails && thumbnails.map( ( thumbnail, index ) => {
					return <Thumbnail
						thumbnailIndex={ thumbnail.index }
						galleryIndex={ galleryIndex }
						isMediumSize={ isMediumSize }
						classes={ {
							li: 'c-gallery-runway-thumbnail',
							link: 'c-gallery-runway-thumbnail__link',
							image: 'c-gallery-runway-thumbnail__image',
							thumbCount: 'c-gallery-runway-thumbnail__count',
						} }
						showThumbnailCount={ true }
						navigationLocked={ navigationLocked }
						size="thumbnail"
						key={ index }
						updateGalleryIndex={ this.props.updateGalleryIndex }
						toggleThumbnailActiveState={ this.props.toggleThumbnailActiveState }
						{ ...thumbnail }
					/>;
				} ) }
			</ul>
		);
	}
}

ThumbnailsBatch.defaultProps = {
	thumbnails: [],
	galleryIndex: 0,
	isMediumSize: false,
	canShow: false,
	batchIndex: 0,
	navigationLocked: false,
};

export default ThumbnailsBatch;
