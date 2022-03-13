import React from 'react';
import SocialIcons from '../social-icons/index';
import DOMPurify from 'dompurify';

import { isEmpty } from 'underscore';

class IntroCard extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.state = {
			showFullContent: false,
		};

		this.toggleContent = this.toggleContent.bind( this );
	}

	/**
	 * Toggle content.
	 *
	 * @param {object} event Event object.
	 *
	 * @return {void}
	 */
	toggleContent( event ) {
		event.preventDefault();

		this.setState( {
			showFullContent: ! this.state.showFullContent,
		} );
	}

	render() {
		const { vertical, date, title, content, excerpt, isMediumSize, i10n, galleryTitle, socialIcons, twitterUserName } = this.props;

		let cardContent = this.state.showFullContent ? content : excerpt;

		if ( ! isMediumSize ) {
			cardContent = content;
		}

		return (
			<div className="c-gallery-intro-card">
				<a onClick={ this.props.closeIntroCard } href="/" className="c-gallery-intro-card__close-icon u-gallery-close-icon u-gallery-close-icon--small-black" >
					<span className="u-gallery-screen-reader-text">{ i10n.closeThisMessage }</span>
				</a>
				<div className="c-gallery-intro-card__header">
					<div className="c-gallery-intro-card__slide-meta">
						{ ! isEmpty( vertical ) && (
							<a href={ vertical.link } className="c-gallery-intro-card__vertical">{ vertical.name }</a>
						) }
						<div className="c-gallery-intro-card__date">{ date }</div>
					</div>
					<h2 className="c-gallery-intro-card__intro-title">{ title }</h2>
				</div>
				<div className="c-gallery-intro-card__content" >
					<span dangerouslySetInnerHTML={ {
						__html: DOMPurify.sanitize( cardContent ),
					} } />
					{ isMediumSize && cardContent && (
						<a className="c-gallery-intro-card__read-more" onClick={ this.toggleContent } href="/">{ this.state.showFullContent ? i10n.showLess : i10n.readMore }</a>
					) }
				</div>
				<button onClick={ this.props.closeIntroCard } className="c-gallery-intro-card__button">{ i10n.startSlideShow }</button>
				<SocialIcons
					socialIcons={ socialIcons }
					twitterUserName={ twitterUserName }
					location={ window.location.href }
					slideTitle={ galleryTitle }
					linkClassPrefix="u-gallery-social-icon u-gallery"
					liClassName="c-gallery-intro-card__social-icon"
					ulClassName="c-gallery-intro-card__social-icons"
				/>
			</div>
		);
	}
}

IntroCard.defaultProps = {
	vertical: {},
	date: '',
	title: '',
	content: '',
	galleryTitle: '',
	isMediumSize: false,
	i10n: {
		startSlideShow: '',
		closeThisMessage: '',
		readMore: '',
		showLess: '',
	},
};

export default IntroCard;
