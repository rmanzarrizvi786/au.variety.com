import React, { Fragment } from 'react';
import Share from './../../svg/share';
import DOMPurify from 'dompurify';
import SocialIcons from './../../social-icons';
import ResponsiveImage from '../../responsive-image';
import ResponsiveVideoImage from '../../responsive-video-image';
import { CSSTransitionGroup } from 'react-transition-group';

DOMPurify.setConfig( { ADD_ATTR: [ 'target' ] } );

class SlideAlbum extends React.Component {
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
			displaySocialIcons: ! props.socialIconsUseMenu,
		};

		this.toggleSocialIcons = this.toggleSocialIcons.bind( this );
	}

	/**
	 * Toggle social icons.
	 *
	 * @param {Object}  event Click event.
	 * @param {Boolean} state State
	 *
	 * @return {void}
	 */
	toggleSocialIcons( event, state ) {
		event.preventDefault();
		const displaySocialIcons = 'undefined' === typeof state ? ! this.state.displaySocialIcons : state;

		this.setState( {
			displaySocialIcons,
		} );
	}

	render() {
		const {
			ID,
			ordering,
			alt,
			caption,
			title,
			description,
			i10n,
			sizes,
			pinterestUrl,
			socialIcons,
			socialIconsUseMenu,
			twitterUserName,
			location,
			video,
		} = this.props;
		const imageCredit = this.props.image_credit; // eslint-disable-line
		const imageSourceUrl = this.props.image_source_url; // eslint-disable-line
		const innerImageCredit = (
			<span className="c-gallery-vertical-slide__photo-credit">{ imageCredit }</span>
		);

		return (

			<article className="c-gallery-vertical-featured-image">

				<div className="c-gallery-vertical-featured-image__header">
					{ ordering && ordering !== 'none' && (
						<span className="c-gallery-vertical-featured-image__number" style={ this.props.listItemStyles.rankNumberStyle }>
							{ this.props.positionDisplay }
						</span>
					) }
					<h2 className="c-gallery-vertical-featured-image__title" dangerouslySetInnerHTML={ {
						__html: DOMPurify.sanitize( title ),
					} } />
				</div>

				{ '' === video && (
					<ResponsiveImage
						fullWidth={ this.props.fullWidth }
						fullHeight={ this.props.fullHeight }
						sizes={ sizes }
						onFigureClick={ () => {} }
						alt={ alt }
						ID={ ID }
						classes={ {
							figure: 'c-gallery-vertical-featured-image__figure',
							img: 'c-gallery-vertical-featured-image__image u-gallery-react-placeholder-shimmer',
						} }
					>
						<div className="c-gallery-vertical-featured-image__share-icons">
							{ this.state.displaySocialIcons && (
								<CSSTransitionGroup transitionName="c-gallery-vertical-share__animation" transitionEnterTimeout={ 500 } transitionLeaveTimeout={ 500 }>
									<SocialIcons
										socialIcons={ socialIcons }
										twitterUserName={ twitterUserName }
										location={ location }
										slideTitle={ title }
										pinterestUrl={ pinterestUrl }
										linkClassPrefix="u-gallery-social-icon u-gallery"
										liClassName="c-gallery-vertical-featured-image__social-icon"
										ulClassName="c-gallery-vertical-featured-image__social-icons"
									/>
								</CSSTransitionGroup>
							) }
							{ socialIconsUseMenu && (
								<a onClick={ this.toggleSocialIcons } href="/" className="c-gallery-vertical-featured-image__share-icon">
									<Share />
								</a>
							) }
						</div>
					</ResponsiveImage>
				) }

				{ video && (
					<ResponsiveVideoImage
						{ ...this.props }
					/>
				) }

				{ imageCredit && (
					<div className="c-gallery-vertical-slide__photo-credit-wrapper">
						<Fragment>
							{ caption && (
								<div className="c-gallery-vertical-featured-image__caption" dangerouslySetInnerHTML={ {
									__html: DOMPurify.sanitize( caption ),
								} } />
							) }
							<span className="c-gallery-vertical-slide__photo-credit-text">{ i10n.vertical.photo }</span>
							<span className="c-gallery-vertical-slide__colon"> : </span>
							{ imageSourceUrl ? ( <a href={ imageSourceUrl } target="_blank" rel="noopener noreferrer" className="c-gallery-vertical-slide__image-source-url">{ innerImageCredit }</a> ) : innerImageCredit }
						</Fragment>
					</div>
				) }

				<div className="c-gallery-vertical-featured-image__description" dangerouslySetInnerHTML={ {
					__html: DOMPurify.sanitize( description ),
				} } />
			</article>
		);
	}
}

SlideAlbum.defaultProps = {
	i10n: {
		vertical: {
			photo: '',
		},
	},
	image: '',
	alt: '',
	image_credit: '',
	title: '',
	caption: '',
	socialIcons: {},
	twitterUserName: '',
	sizes: {},
	ordering: '',
	video: '',
};

export default SlideAlbum;
