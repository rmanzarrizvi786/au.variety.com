import React, { Fragment } from 'react';
import DOMPurify from 'dompurify';
import ResponsiveImage from '../../responsive-image';
import Share from './../../svg/share';
import SocialIcons from './../../social-icons';
import ErrorBoundary from './../../error-boundary';
import AppleMusicPlayer from './../../apple-music-player';
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
			subtitle,
			appleSongID,
			enableAppleGA,
			description,
			i10n,
			sizes,
			pinterestUrl,
			socialIcons,
			socialIconsUseMenu,
			twitterUserName,
			location,
		} = this.props;
		const imageCredit = this.props.image_credit; // eslint-disable-line
		const imageSourceUrl = this.props.image_source_url; // eslint-disable-line
		const innerImageCredit = (
			<span className="c-gallery-vertical-slide__photo-credit">{ imageCredit }</span>
		);

		return (

			<article className="c-gallery-vertical-album">
				<span className="c-gallery-vertical-album__figure-wrapper">
					<ResponsiveImage
						fullWidth={ this.props.fullWidth }
						fullHeight={ this.props.fullHeight }
						sizes={ sizes }
						onFigureClick={ () => {} }
						alt={ alt }
						ID={ ID }
						classes={ {
							figure: 'c-gallery-vertical-album__figure',
							img: 'c-gallery-vertical-album__image u-gallery-react-placeholder-shimmer',
						} }
					>
						<div className="c-gallery-vertical-featured-image__share-icons">
							<CSSTransitionGroup transitionName="c-gallery-vertical-share__animation" transitionEnterTimeout={ 500 } transitionLeaveTimeout={ 500 }>
								{ this.state.displaySocialIcons && (
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
								) }
							</CSSTransitionGroup>
							{ socialIconsUseMenu && (
								<a onClick={ this.toggleSocialIcons } href="/" className="c-gallery-vertical-featured-image__share-icon">
									<Share />
								</a>
							) }
						</div>
					</ResponsiveImage>
					{ imageCredit && (
						<div className="c-gallery-vertical-slide__photo-credit-wrapper">
							<Fragment>
								<span className="c-gallery-vertical-slide__photo-credit-text">{ i10n.vertical.photo }</span>
								<span className="c-gallery-vertical-slide__colon">: </span>
								{ imageSourceUrl ? ( <a href={ imageSourceUrl } target="_blank" rel="noopener noreferrer" className="c-gallery-vertical-slide__image-source-url">{ innerImageCredit }</a> ) : innerImageCredit }
							</Fragment>
						</div>
					) }
				</span>

				{ ordering && ordering !== 'none' && (
					<span className="c-gallery-vertical-album__number" style={ this.props.listItemStyles.rankNumberStyle }>
						{ this.props.positionDisplay }
					</span>
				) }

				<h2 className="c-gallery-vertical-album__title" dangerouslySetInnerHTML={ {
					__html: DOMPurify.sanitize( title ),
				} } />

				{ caption && (
					<div className="c-gallery-vertical-album__caption" dangerouslySetInnerHTML={ {
						__html: DOMPurify.sanitize( caption ),
					} } />
				) }

				{ subtitle && (
					<div className="c-gallery-vertical-album__subtitle" dangerouslySetInnerHTML={ {
						__html: DOMPurify.sanitize( subtitle ),
					} } />
				) }

				{ appleSongID && (
					<ErrorBoundary>
						<AppleMusicPlayer song={ appleSongID } enableAnalytics={ enableAppleGA } />
					</ErrorBoundary>
				) }

				<div className="c-gallery-vertical-album__description" dangerouslySetInnerHTML={ {
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
	sizes: {},

};

export default SlideAlbum;
