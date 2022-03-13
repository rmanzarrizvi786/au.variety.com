import React from 'react';

import { delay, debounce } from 'underscore';

import ThumbnailsBatch from './thumbnails-batch';
import ThumbnailsIcon from './../../svg/thumbnails';

/**
 * Thumbnails column component for runway gallery.
 */
class Thumbnails extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.thumbnailsInBatch = 8;
		this.thumbnailsBatches = this.createThumbnailsBatches();
		this.lastBatchIndex = ( this.thumbnailsBatches.length - 1 );
		this.thumbnailEventType = '';

		// Debounce as the component might update more than once.
		this.moveToActiveThumbnailsBatchDebounced = debounce( this.moveToActiveThumbnailsBatch, 100 );

		this.state = {
			currentBatchIndex: 0,
		};
	}

	/**
	 * Create thumbnails batches.
	 *
	 * @return {Array} Thumbnail batches.
	 */
	createThumbnailsBatches() {
		const thumbnails = this.props.thumbnails;
		const batches = [];
		const lastIndex = thumbnails.length - 1;
		let batch = [];

		if ( 0 === thumbnails.length ) {
			return batches;
		}

		thumbnails.forEach( ( thumbnail, index ) => {
			if ( batch.length && 0 === index % this.thumbnailsInBatch ) {
				batches.push( batch );
				batch = [];
			}

			thumbnail.index = index;
			batch.push( thumbnail );

			// Grab the last batch.
			if ( batch.length && index === lastIndex ) {
				batches.push( batch );
			}
		} );

		return batches;
	}

	/**
	 * When component updates.
	 *
	 * @return {void}
	 */
	componentDidUpdate() {
		this.moveToActiveThumbnailsBatchDebounced( this.props.galleryIndex );
	}

	/**
	 * Move thumbnails batch to active thumbnail.
	 *
	 * @param {int} galleryIndex Gallery/Thumbnail index.
	 *
	 * @return {void}
	 */
	moveToActiveThumbnailsBatch( galleryIndex ) {
		let activeThumbnailBatchIndex = null;

		// Bail out if component was updated due to arrow click.
		if ( 'click' === this.thumbnailEventType ) {
			delay( () => this.thumbnailEventType = '', 200 );
			return;
		}

		this.thumbnailsBatches.forEach( ( thumbnails, batchIndex ) => {
			thumbnails.forEach( ( thumbnail ) => {
				if ( galleryIndex === thumbnail.index ) {
					activeThumbnailBatchIndex = batchIndex;
				}
			} );
		} );

		if ( activeThumbnailBatchIndex !== this.state.currentBatchIndex ) {
			this.setState( {
				currentBatchIndex: activeThumbnailBatchIndex,
			} );
		}
	}

	/**
	 * Scroll up the thumbnail container.
	 *
	 * @param {Object} event     Event object.
	 * @param {String} direction Scroll direction up/down
	 *
	 * @return {void}
	 */
	scrollThumbnails( event, direction ) {
		let currentBatchIndex = this.state.currentBatchIndex;

		event.preventDefault();

		this.thumbnailEventType = 'click';

		if ( 'up' === direction && currentBatchIndex > 0 ) {
			currentBatchIndex -= 1;
		} else if ( 'down' === direction && currentBatchIndex !== this.lastBatchIndex ) {
			currentBatchIndex += 1;
		}

		this.setState( {
			currentBatchIndex,
		} );
	}

	render() {
		const { galleryIndex, isMediumSize, i10n, navigationLocked } = this.props;

		const canShowUpArrow = ( 0 !== this.state.currentBatchIndex );
		const canShowDownArrow = ( this.state.currentBatchIndex !== this.lastBatchIndex && this.thumbnailsBatches.length > 1 );
		const thumbnailClassName = ! canShowUpArrow ? 'c-gallery-runway__thumbnails--top' : '';

		return (
			<aside className={ [ 'c-gallery-runway-thumbnails c-gallery-runway__thumbnails', thumbnailClassName ].join( ' ' ) } >
				<div className="c-gallery-runway-thumbnails__header">
					<a onClick={ event => this.scrollThumbnails( event, 'up' ) } href="/" className="u-gallery-arrow-big u-gallery-arrow-big--up c-gallery-runway-thumbnails__scroll-up">
						<span className="u-gallery-screen-reader-text">{ i10n.scrollUp }</span>
					</a>
				</div>
				<div className="c-gallery-runway-thumbnails__container">
					{ this.thumbnailsBatches.length && this.thumbnailsBatches.map( ( thumbnails, batchIndex ) => {
						const canShow = batchIndex === this.state.currentBatchIndex;

						return (
							<ThumbnailsBatch
								thumbnails={ thumbnails }
								key={ batchIndex }
								canShow={ canShow }
								galleryIndex={ galleryIndex }
								isMediumSize={ isMediumSize }
								updateGalleryIndex={ this.props.updateGalleryIndex }
								toggleThumbnailActiveState={ this.props.toggleThumbnailActiveState }
								navigationLocked={ navigationLocked }
							/>
						);
					} ) }
				</div>
				<div className="c-gallery-runway-thumbnails__footer">
					<a onClick={ this.props.toggleThumbnailActiveState } href="/" className="c-gallery-runway-thumbnails__lightbox-icon">
						<ThumbnailsIcon />
						<span className="c-gallery-runway-thumbnails__thumbnail-text">{ i10n.lightBox }</span>
					</a>
					{ canShowDownArrow && (
						<a onClick={ event => this.scrollThumbnails( event, 'down' ) } href="/" className="u-gallery-arrow-big u-gallery-arrow-big--down c-gallery-runway-thumbnails__scroll-down">
							<span className="u-gallery-screen-reader-text">{ i10n.scrollDown }</span>
						</a>
					) }
				</div>
			</aside>
		);
	}
}

Thumbnails.defaultProps = {
	thumbnails: [],
	i10n: {
		lightBox: '',
		scrollDown: '',
		scrollUp: '',
	},
	galleryIndex: 0,
	isMediumSize: false,
	navigationLocked: false,
};

export default Thumbnails;
