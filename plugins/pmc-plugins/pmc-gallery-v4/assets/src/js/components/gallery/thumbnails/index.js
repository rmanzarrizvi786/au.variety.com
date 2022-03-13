import React from 'react';

import Thumbnail from './../../thumbnail';

/**
 * Thumbnails Component.
 */
class Thumbnails extends React.Component {
	/**
	 * Render Component.
	 *
	 * @return {*} Component object.
	 */
	render() {
		const { thumbnails, galleryIndex, isMediumSize, navigationLocked } = this.props;

		return (
			<ul className="c-gallery-thumbnails__list" >
				{ thumbnails && thumbnails.map( ( thumbnail, index ) => {
					return <Thumbnail
						thumbnailIndex={ index }
						galleryIndex={ galleryIndex }
						isMediumSize={ isMediumSize }
						key={ index }
						navigationLocked={ navigationLocked }
						updateGalleryIndex={ this.props.updateGalleryIndex }
						toggleThumbnailActiveState={ this.props.toggleThumbnailActiveState }
						{ ...thumbnail }
					/>;
				} ) }
			</ul>
		);
	}
}

Thumbnails.defaultProps = {
	thumbnails: [],
	i10n: {},
	galleryIndex: 0,
	isMediumSize: false,
	navigationLocked: false,
};

export default Thumbnails;
