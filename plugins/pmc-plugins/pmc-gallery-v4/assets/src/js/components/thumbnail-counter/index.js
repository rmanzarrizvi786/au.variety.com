import React from 'react';

import ThumbnailsIcon from './../svg/thumbnails';

/**
 * ThumbnailCounter component for gallery.
 */
class ThumbnailCounter extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.onEscape = this.onEscape.bind( this );
	}

	/**
	 * Attach event on component mount.
	 *
	 * @return {void}
	 */
	componentDidMount() {
		document.addEventListener( 'keydown', this.onEscape, false );
	}

	/**
	 * Remove event when component un-mounts.
	 *
	 * @return {void}
	 */
	componentWillUnmount() {
		document.removeEventListener( 'keydown', this.onEscape, false );
	}

	/**
	 * Hide thumbnails on esc key press.
	 *
	 * @param {object} event Key down event.
	 *
	 * @return {void}
	 */
	onEscape( event ) {
		if ( 'Escape' === event.key && this.props.isThumbnailsActive ) {
			this.props.toggleThumbnailActiveState( event );
		}
	}

	render() {
		const { currentCount, totalCount, i10n } = this.props;

		return (
			<div className="c-gallery-thumbnail-counter">
				<p className="c-gallery-thumbnail-counter__title">{ i10n.thumbnail }</p>
				<div className="c-gallery-thumbnail-counter__bottom">
					<div className="c-gallery-thumbnail-counter__count">
						<span className="c-gallery-thumbnail-counter__current">{ currentCount }</span>
						<span className="c-gallery-thumbnail-counter__divider"> { i10n.of } </span>
						<span className="c-gallery-thumbnail-counter__total">{ totalCount }</span>
					</div>
					<a onClick={ this.props.toggleThumbnailActiveState } href="/" className="c-gallery-thumbnail-counter__icon">
						<ThumbnailsIcon />
					</a>
				</div>
			</div>
		);
	}
}

ThumbnailCounter.defaultProps = {
	currentCount: 0,
	totalCount: 0,
	i10n: {},
};

export default ThumbnailCounter;
