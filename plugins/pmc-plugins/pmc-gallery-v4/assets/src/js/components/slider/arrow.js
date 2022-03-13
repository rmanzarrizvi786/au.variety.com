import React from 'react';

import { isFunction } from 'underscore';

import { trackGA } from './../../utils/index';

/**
 * Arrow component for gallery.
 */
class Arrow extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.onClick = this.onClick.bind( this );
	}

	/**
	 * Get nav link.
	 *
	 * @return {string} Nav link.
	 */
	getNavLink() {
		let navLink = '/';

		const { galleryIndex, previousGalleryLink, nextGallery, slideCount, type, to } = this.props;

		if ( 'interstitial' === type ) {
			return navLink;
		}

		if ( 0 === galleryIndex && 'prev' === to ) {
			navLink = previousGalleryLink || '';
		} else if ( galleryIndex === ( slideCount - 1 ) && 'next' === to ) {
			navLink = nextGallery.link || '';
		}

		return navLink;
	}

	onClick( event ) {
		const link = event.currentTarget.getAttribute( 'href' );

		if ( 'function' === typeof this.props.onClick ) {
			this.props.onClick( event ); // This onClick is passed by slick JS internally.
		} else if ( isFunction( this.props.onExternalArrowClick ) ) {
			this.props.onExternalArrowClick( event );
		}

		// Is last slide.
		if ( link === this.props.nextGallery.link ) {
			event.preventDefault();

			if ( this.props.canShowEndSlide ) {
				this.props.toggleEndSlide( null, true );

				return;
			}

			trackGA( {
				hitType: 'event',
				eventCategory: 'standard-gallery',
				eventAction: 'click',
				eventLabel: this.props.nextGallery.type,
				nonInteraction: true,
			} );

			window.location.href = link;
		}
	}

	render() {
		const { to, arrowClass } = this.props;
		const navLink = this.getNavLink();

		return navLink ? (
			<a href={ navLink } onClick={ this.onClick } className={ arrowClass } >
				<span className="u-gallery-screen-reader-text">{ to }</span>
			</a>
		) : null;
	}
}

Arrow.defaultProps = {
	to: 'next',
	arrowClass: '',
	nextGallery: {
		link: '',
		type: '',
	},
	previousGalleryLink: '',
	type: 'slider',
	galleryIndex: 0,
	slideCount: 0,
	canShowEndSlide: false,
};

export default Arrow;
