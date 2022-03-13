import React from 'react';
import { LazyLoadImage } from 'react-lazy-load-image-component';

import { trackGA } from '../../utils';

/**
 * Thumbnail component for gallery.
 */
class Thumbnail extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.updateGalleryIndex = this.updateGalleryIndex.bind( this );
	}

	/**
	 * Updates slide index.
	 *
	 * @param {Object} event Click event object.
	 *
	 * @return {void}
	 */
	updateGalleryIndex( event ) {
		event.preventDefault();

		if ( this.props.navigationLocked ) {
			return;
		}

		this.props.updateGalleryIndex( this.props.thumbnailIndex );

		if ( this.props.isMediumSize || this.props.updateActiveStateOnClick ) {
			this.props.toggleThumbnailActiveState( event );
			window.scrollTo( 0, 0 );
		}

		trackGA( {
			hitType: 'event',
			eventCategory: 'standard-gallery',
			eventAction: 'click',
			eventLabel: 'lightbox-thumbnail',
		} );
	}

	/**
	 * Render component.
	 *
	 * @return {*} Component Object.
	 */
	render() {
		const { title, url, slug, galleryIndex, thumbnailIndex, sizes, size, isMediumSize, classes, showThumbnailCount, wrapLi } = this.props;
		const thumbnailClass = galleryIndex === thumbnailIndex ? classes.li + ' u-gallery-active' : classes.li;
		const slideUrl = `${ url }${ slug }/`;
		let thumbnail = isMediumSize ? sizes[ 'pmc-gallery-s' ] : sizes.thumbnail;

		thumbnail = size ? sizes[ size ] : thumbnail;

		const image = (
			<LazyLoadImage
				className={ classes.image }
				width={ thumbnail.width }
				height={ thumbnail.height }
				src={ thumbnail.src }
				alt=""
				delayTime={ 1000 }
				threshold={ 300 }
			/>
		);

		const thumbnailLink = (
			<button className={ classes.link } href={ slideUrl } onClick={ this.updateGalleryIndex } aria-label={ title } >
				{ image }
				{ showThumbnailCount && (
					<span className={ classes.thumbCount }>{ thumbnailIndex + 1 }</span>
				) }
			</button>
		);

		/* Optional li for creating ref for li easily from parent component */
		if ( wrapLi ) {
			return (
				<li className={ thumbnailClass } >
					{ thumbnailLink }
				</li>
			);
		}

		return thumbnailLink;
	}
}

Thumbnail.defaultProps = {
	sizes: {},
	url: '',
	galleryIndex: 0,
	thumbnailIndex: 0,
	isMediumSize: false,
	showThumbnailCount: false,
	updateActiveStateOnClick: false,
	navigationLocked: false,
	wrapLi: true,
	size: '',
	classes: {
		li: 'c-gallery-thumbnail',
		link: 'c-gallery-thumbnail__link',
		image: 'c-gallery-thumbnail__image',
		thumbCount: 'c-gallery-thumbnail__count',
	},
};

export default Thumbnail;
