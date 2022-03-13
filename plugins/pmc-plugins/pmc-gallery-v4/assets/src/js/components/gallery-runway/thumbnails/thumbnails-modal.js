import React from 'react';

import Thumbnail from './../../thumbnail';
import { isEmpty } from 'underscore';

/**
 * Thumbnails modal.
 */
class ThumbnailsModal extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.thumbnailsContainerRef = React.createRef();
		this.thumbnailRefs = [];

		this.props.thumbnails.forEach( ( thumbnail, index ) => {
			this.thumbnailRefs[ index ] = React.createRef();
		} );
	}

	/**
	 * When component updates.
	 *
	 * @return {void}
	 */
	componentDidMount() {
		this.scrollToActiveThumbnail();
	}

	/**
	 * Scroll light-box modal to active thumbnail.
	 *
	 * @return {void}
	 */
	scrollToActiveThumbnail() {
		const margin = 100;
		const container = this.thumbnailsContainerRef.current;
		const activeThumbnailRef = this.thumbnailRefs[ this.props.galleryIndex ];

		if ( ! container || ! activeThumbnailRef ) {
			return;
		}

		if ( activeThumbnailRef.current ) {
			const top = activeThumbnailRef.current.offsetTop;
			container.scrollTop = ( top > margin ) ? top - margin : 0;
		}
	}

	render() {
		const { thumbnails, galleryIndex, isMediumSize, i10n, nextGallery } = this.props;
		const nextGalleryThumbnail = ( ! isEmpty( nextGallery ) && ! isEmpty( nextGallery.thumbnail ) ) ? nextGallery.thumbnail : null;

		return (
			<div ref={ this.thumbnailsContainerRef } className="c-gallery-runway-thumbnails-modal u-gallery-modal">
				<ul className="c-gallery-runway-thumbnails-modal__list" >
					{ thumbnails && thumbnails.map( ( thumbnail, index ) => {
						const thumbnailClass = galleryIndex === index ? 'c-gallery-runway-thumbnails-modal__thumbnail u-gallery-active' : 'c-gallery-runway-thumbnails-modal__thumbnail';

						return (
							<li ref={ this.thumbnailRefs[ index ] } className={ thumbnailClass } key={ index } >
								<Thumbnail
									thumbnailIndex={ index }
									galleryIndex={ galleryIndex }
									isMediumSize={ isMediumSize }
									updateActiveStateOnClick={ true }
									classes={ {
										link: 'c-gallery-runway-thumbnail__link c-gallery-runway-thumbnail__link--modal',
										image: 'c-gallery-runway-thumbnail__image c-gallery-runway-thumbnail__image--modal',
										thumbCount: 'c-gallery-runway-thumbnail__count c-gallery-runway-thumbnail__count--modal',
									} }
									showThumbnailCount={ true }
									wrapLi={ false }
									size="pmc-gallery-s"
									updateGalleryIndex={ this.props.updateGalleryIndex }
									toggleThumbnailActiveState={ this.props.toggleThumbnailActiveState }
									{ ...thumbnail }
								/>
							</li>
						);
					} ) }
					{ nextGalleryThumbnail && (
						<li key={ nextGalleryThumbnail.ID } className="c-gallery-runway-thumbnails-modal__thumbnail">
							<a className="c-gallery-runway-thumbnail__link" href={ nextGallery.link }>
								<img className="c-gallery-runway-thumbnail__image c-gallery-runway-thumbnail__next-gallery-image" src={ nextGalleryThumbnail.src } alt={ nextGalleryThumbnail.alt } />
								<span className="c-gallery-runway-thumbnails-modal__next-gallery">{ i10n.nextGallery }</span>
							</a>
						</li>
					) }
				</ul>
				<button onClick={ this.props.toggleThumbnailActiveState } className="c-gallery-runway-thumbnails-modal__close u-gallery-close-icon">
					<span className="u-gallery-screen-reader-text">{ i10n.closeModal }</span>
				</button>
			</div>
		);
	}
}

ThumbnailsModal.defaultProps = {
	thumbnails: [],
	i10n: {
		nextGallery: '',
		closeModal: '',
	},
	galleryIndex: 0,
	isMediumSize: false,
};

export default ThumbnailsModal;
