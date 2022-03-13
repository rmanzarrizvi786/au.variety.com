import React, { Fragment } from 'react';
import { CSSTransitionGroup } from 'react-transition-group';

/**
 * EndSlide component for gallery.
 */
class EndSlide extends React.Component {
	render() {
		const { i10n, subscriptionsLink, toggleEndSlide, displayEndSlide, nextGallery } = this.props;

		return (
			<CSSTransitionGroup transitionName="c-gallery-end-slide__animation" transitionEnterTimeout={ 500 } transitionLeaveTimeout={ 400 } >
				{ displayEndSlide && (
					<div className="c-gallery-end-slide" >
						<div className="c-gallery-end-slide__inner">
							<div className="c-gallery-end-slide__next-gallery-container">
								{ subscriptionsLink && (
									<a href={ subscriptionsLink } className="c-gallery-end-slide__subscribe-ad">
										<span className="c-gallery-end-slide__subscribe-uppertext">{ i10n.missingSomething }</span>
										<span className="c-gallery-end-slide__subscribe-text">{ i10n.subscribeNow }</span>
									</a>
								) }

								{ nextGallery && nextGallery.title && (
									<Fragment>
										<div className="c-gallery-end-slide__next-text">{ i10n.next }</div>
										<h2 className="c-gallery-end-slide__next-slide-title">
											<a href={ nextGallery.link }>{ nextGallery.title }</a>
										</h2>
									</Fragment>
								) }

								<div className="c-gallery-end-slide__close-container">
									<button onClick={ toggleEndSlide } className="c-gallery-end-slide__close-message">{ i10n.closeThisMessage }</button>
								</div>
							</div>
						</div>
					</div>
				) }
			</CSSTransitionGroup>
		);
	}
}

EndSlide.defaultProps = {
	i10n: {
		missingSomething: '',
		subscribeNow: '',
		next: '',
		closeThisMessage: '',
	},
	subscriptionsLink: '',
	displayEndSlide: false,
	nextGallery: {
		title: '',
		link: '',
	},
};

export default EndSlide;
