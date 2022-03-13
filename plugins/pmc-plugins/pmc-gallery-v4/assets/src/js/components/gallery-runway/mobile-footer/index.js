import React from 'react';

import DotsMenu from './../../svg/dots-menu';

class MobileFooter extends React.Component {
	render() {
		const { i10n, subscriptionsLink, titleClass, galleryTitle } = this.props;

		return (
			<footer className="c-gallery-runway-mobile-footer">
				<h1 className={ titleClass }>
					{ galleryTitle }
				</h1>
				<a onClick={ this.props.toggleMobileMenu } className="c-gallery-runway-mobile-footer__menu-icon" href="/">
					<DotsMenu />
				</a>
				<div className="c-gallery-runway-mobile-footer__byline" />
				{ subscriptionsLink && ( <a className="c-gallery-runway-sidebar-left__subscription-link" href={ subscriptionsLink }><span className="c-gallery-runway-sidebar-left__missing-something">{ i10n.missingSomething }</span><span className="c-gallery-runway-sidebar-left__subscribe-now">{ i10n.subscribeNow }</span></a>
				) }
			</footer>
		);
	}
}

MobileFooter.defaultProps = {
	subscriptionsLink: '',
	i10n: {
		missingSomething: '',
		subscribeNow: '',
	},
	titleClass: '',
	galleryTitle: '',
};

export default MobileFooter;
