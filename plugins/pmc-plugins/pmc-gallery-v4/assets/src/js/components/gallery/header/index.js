import React, { Fragment } from 'react';

import { isEmpty, isObject } from 'underscore';

import SocialIcons from './../../social-icons';
import CloseIcon from './../../svg/close-icon';
import ThumbnailCounter from './../../thumbnail-counter';
import DOMPurify from 'dompurify';

/**
 * Header Component for gallery.
 */
class Header extends React.Component {
	render() {
		const {
			logo,
			siteUrl,
			galleryTitle,
			siteTitle,
			slideTitle,
			i10n,
			closeButtonLink,
			location,
			isMediumSize,
			showThumbnails,
			totalCount,
			currentCount,
			pinterestUrl,
			sponsored,
			sponsoredStyle,
			styles,
			thumbnailsActive,
			isThumbnailsActive,
			socialIcons,
			twitterUserName,
			mobileCloseButton,
		} = this.props;

		const titleClass = isEmpty( sponsored ) ? 'c-gallery-header__title u-gallery-center' : 'c-gallery-header__title u-gallery-center c-gallery-header__title-with-sponsored-text';
		const logoLink = isObject( logo ) && logo.link ? logo.link : siteUrl;
		const logoAlt = '';
		const headerRightClass = mobileCloseButton ? 'c-gallery-header__right c-gallery-header__right--has-close-button u-gallery-center' : 'c-gallery-header__right u-gallery-center';

		return (
			<div className="c-gallery-header">
				<h2 className="c-gallery-header__logo">
					<a className="c-gallery-header__logo-link u-gallery-center" href={ logoLink } >
						<span className="u-gallery-screen-reader-text c-gallery-header__site-title">{ siteTitle }</span>
						{ isObject( logo ) && logo.src && (
							<img className="c-gallery-header__logo-image" alt={ logoAlt } src={ logo.src } width={ logo.width } height={ logo.height } />
						) }
					</a>
				</h2>
				{ ! isMediumSize && (
					<h1 className={ titleClass }>
						<span style={ styles[ 'horizontal-header-title-style' ] } dangerouslySetInnerHTML={ {
							__html: DOMPurify.sanitize( galleryTitle ),
						} } />
						{ ! isEmpty( sponsored ) && ( <span className="c-gallery-header__sponsored"><span className="c-gallery-header__sponsored-text" style={ sponsoredStyle }>{ sponsored }</span></span>
						) }
					</h1>
				) }
				<div className={ headerRightClass } >
					{ ! isMediumSize && (
						<SocialIcons
							location={ location }
							slideTitle={ slideTitle }
							socialIcons={ socialIcons }
							twitterUserName={ twitterUserName }
							pinterestUrl={ pinterestUrl }
							linkClassPrefix="c-gallery-social-icons__icon"
							ulClassName="c-gallery-social-icons"
							liClassName="c-gallery-social-icons__icon"
						>
							{ closeButtonLink && (
								<li className="c-gallery-social-icons__icon c-gallery-header__back-to-linked-post">
									<a className="c-gallery-header__back-link" href={ closeButtonLink }>
										<CloseIcon title={ i10n.closeGallery } />
									</a>
								</li>
							) }
						</SocialIcons>
					) }
					{ isMediumSize && (
						<Fragment>
							<div className="c-gallery-header__counter">
								{ showThumbnails && (
									<ThumbnailCounter
										i10n={ i10n }
										totalCount={ totalCount }
										currentCount={ currentCount }
										thumbnailsActive={ thumbnailsActive }
										toggleThumbnailActiveState={ this.props.toggleThumbnailActiveState }
										isThumbnailsActive={ isThumbnailsActive }
									/>
								) }
							</div>
							{ mobileCloseButton && (
								<a className="c-gallery-header__mobile-close-link" href={ closeButtonLink }>
									<img className="c-gallery-header__mobile-close-button" src={ mobileCloseButton } alt={ i10n.closeGallery } />
								</a>
							) }
						</Fragment>
					) }
				</div>
			</div>
		);
	}
}

Header.defaultProps = {
	logo: {},
	siteUrl: '',
	galleryTitle: '',
	slideTitle: '',
	closeButtonLink: '',
	location: '',
	socialIcons: {},
	twitterUserName: '',
	isMediumSize: false,
	showThumbnails: true,
	mobileCloseButton: '',
	sponsored: '',
	sponsoredStyle: {},
	styles: {},
	i10n: {
		closeGallery: '',
	},
};

export default Header;
