import React from 'react';
import Counter from './../sidebar-left/counter';
import ThumbnailsIcon from './../../svg/thumbnails';

import { isObject } from 'underscore';

class MobileHeader extends React.Component {
	render() {
		const { siteUrl, siteTitle, logo, i10n, galleryIndex, totalCount, closeButtonLink } = this.props;

		return (
			<header className="c-gallery-runway-mobile-header">
				<h2 className="c-gallery-runway-mobile-header__logo">
					<a className="c-gallery-runway-mobile-header__logo-link" href={ siteUrl } title={ siteTitle } >
						<span className="u-gallery-screen-reader-text c-gallery-runway-mobile-header__site-title">{ siteTitle }</span>
						{ isObject( logo ) && logo.src && (
							<img className="c-gallery-runway-mobile-header__logo-image" alt={ siteTitle } { ...logo } />
						) }
					</a>
				</h2>
				<div className="c-gallery-run-mobile-header__counter">
					<Counter
						currentSlide={ galleryIndex + 1 }
						totalSlide={ totalCount }
						i10n={ i10n }
						isMediumSize={ true }
					/>
				</div>
				<a title={ i10n.lightBox } onClick={ this.props.toggleThumbnailActiveState } href="/" className="c-gallery-runway-mobile-header__lightbox-icon c-gallery-runway-thumbnails__lightbox-icon">
					<ThumbnailsIcon />
					<span className="u-gallery-screen-reader-text">{ i10n.lightBox }</span>
				</a>
				<a href={ closeButtonLink } className="c-gallery-runway-mobile-header__close u-gallery-close-icon">
					<span className="u-gallery-screen-reader-text">{ i10n.backToReview }</span>
				</a>
			</header>
		);
	}
}

MobileHeader.defaultProps = {
	siteUrl: '',
	siteTitle: '',
	logo: {},
	i10n: {
		backToReview: '',
		backToAllReviews: '',
		closeGallery: '',
		lightBox: '',
	},
	galleryIndex: 0,
	totalCount: 0,
	closeButtonLink: '',
};

export default MobileHeader;
