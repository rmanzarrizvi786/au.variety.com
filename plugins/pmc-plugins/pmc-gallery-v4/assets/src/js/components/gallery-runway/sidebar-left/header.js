import React from 'react';
import { isObject } from 'underscore';

/**
 * Header component for runway gallery.
 */
class Header extends React.Component {
	render() {
		const { siteUrl, siteTitle, logo, closeButtonLink, i10n } = this.props;

		return (
			<header className="c-gallery-runway-header">
				<h2 className="c-gallery-runway-header__logo">
					<a className="c-gallery-runway-header__logo-link" href={ siteUrl } title={ siteTitle } >
						<span className="u-gallery-screen-reader-text c-gallery-runway-header__site-title">{ siteTitle }</span>
						{ isObject( logo ) && logo.src && (
							<img className="c-gallery-runway-header__logo-image" alt={ siteTitle } { ...logo } />
						) }
					</a>
				</h2>
				<a className="c-gallery-runway-header__back-link" href={ closeButtonLink }>
					<span className="c-gallery-runway-header__back-link-icon u-gallery-arrow-small u-gallery-arrow-small--left" />
					{ i10n.backToReview }
				</a>
			</header>
		);
	}
}

Header.defaultProps = {
	siteUrl: '',
	siteTitle: '',
	logo: {},
	i10n: {
		backToReview: '',
	},
	closeButtonLink: '',
};

export default Header;
