import React from 'react';

import Header from './header';
import Counter from './counter';
import Menu from './menu';

/**
 * Sidebar left component for runway gallery.
 */
class SidebarLeft extends React.Component {
	render() {
		const {
			siteUrl,
			siteTitle,
			logo,
			closeButtonLink,
			i10n,
			galleryIndex,
			totalCount,
			runwayMenu,
			subscriptionsLink,
			isMediumSize,
			galleryTitle,
			imageCredit,
		} = this.props;

		const titleClass = 'c-gallery-sidebar__slide-title';

		return (
			<div className="c-gallery-runway-sidebar-left c-gallery-runway__sidebar-left">
				<Header
					siteUrl={ siteUrl }
					siteTitle={ siteTitle }
					logo={ logo }
					i10n={ i10n }
					closeButtonLink={ closeButtonLink }
				/>
				<nav className="c-gallery-runway-nav c-gallery-runway-sidebar-left__nav" >
					<Menu
						menu={ runwayMenu }
					/>
				</nav>
				<footer className="c-gallery-runway-sidebar-left__bottom">
					{ ! isMediumSize && ( <h1 className={ titleClass }>
						{ galleryTitle }
					</h1> ) }
					{ subscriptionsLink && ( <a className="c-gallery-runway-sidebar-left__subscription-link" href={ subscriptionsLink }><span className="c-gallery-runway-sidebar-left__missing-something">{ i10n.missingSomething }</span><span className="c-gallery-runway-sidebar-left__subscribe-now">{ i10n.subscribeNow }</span></a>
					) }
					<Counter
						currentSlide={ galleryIndex + 1 }
						totalSlide={ totalCount }
						i10n={ i10n }
					/>
					<div className="c-gallery-runway-sidebar-left__slide-arrows">
						{ this.props.prevArrow }
						{ this.props.nextArrow }
					</div>
					<div className="c-gallery-runway-sidebar-left__image-credit">
						{ imageCredit }
					</div>
				</footer>
			</div>
		);
	}
}

SidebarLeft.defaultProps = {
	siteUrl: '',
	siteTitle: '',
	logo: {},
	closeButtonLink: '',
	galleryIndex: 0,
	totalCount: 0,
	runwayMenu: {},
	i10n: {
		missingSomething: '',
		subscribeNow: '',
		of: '',
		look: '',
		nextSlide: '',
		prevSlide: '',
	},
	subscriptionsLink: '',
	isMediumSize: '',
	galleryTitle: '',
	imageCredit: '',
};

export default SidebarLeft;
