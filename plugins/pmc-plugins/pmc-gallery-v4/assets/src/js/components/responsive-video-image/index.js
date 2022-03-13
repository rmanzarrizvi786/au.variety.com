import React from 'react';
import Share from './../svg/share';
import SocialIcons from './../social-icons';
import ResponsiveImage from '../responsive-image';
import { CSSTransitionGroup } from 'react-transition-group';
import { default as VideoCrop } from './VideoCrop';

/**
 * Responsive image component to be used in all galleries.
 */

class ResponsiveVideoImage extends React.Component {
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

		this.video = {};

		this.toggleSocialIcons = this.toggleSocialIcons.bind( this );

		this.launchVideoRef = React.createRef();
	}

	/**
	 * When component mounts.
	 *
	 * @return {void}
	 */
	componentDidMount() {
		this.root = document.querySelector( '[data-pmc-gallery-video]' );

		this.createZoom();
	}

	/**
	 * Create zoom.
	 *
	 * @return {void}
	 */
	createZoom() {
		const modal = this.launchVideoRef.current;

		if ( modal ) {
			this.video = new VideoCrop( modal );
		}
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
		const { ID, alt, title, sizes, pinterestUrl, socialIcons, socialIconsUseMenu, twitterUserName, location, video } = this.props;
		return (
			<div className="c-list__picture_video_container">
				<div ref={ this.launchVideoRef } className="c-list__picture_video u-gallery-react-placeholder-shimmer" data-pmc-gallery-video>
					<div hidden dangerouslySetInnerHTML={ {
						__html: video,
					} }>
					</div>
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
					</ResponsiveImage>

					{ /* Video Play Button */ }
					<div className="c-card__badge">
						<svg className="c-play-btn c-play-btn--clock c-play-btn--big-clock" viewBox="0 0 88 88">
							<g transform="translate(-.082 -.082)" fill="none" fillRule="evenodd">
								<circle
									className="c-play-btn__fill"
									style={ this.props.listItemStyles.videoPlayButtonStyle }
									cx="44"
									cy="44"
									r="44"
								/>
								<circle
									className="c-play-btn__border"
									style={ this.props.listItemStyles.videoPlayButtonHoverStyle }
									cx="44"
									cy="44"
									r="44"
									strokeDasharray="276"
									strokeDashoffset="276"
								/>
								<path
									className="c-play-btn__icon"
									style={ this.props.listItemStyles.videoPlayButtonIconStyle }
									d="M38.242 28.835c-.634-.467-2.323-.467-2.46 1.298v19.743a.99.99 0 0 0 1.577.796 3.88 3.88 0 0 0 1.577-3.123V33.105l16.008 10.969-18.458 12.61c-.44.3-.703.798-.703 1.331a1.564 1.564 0 0 0 2.46 1.298l20.383-13.941c.528-.317 1.252-1.615 0-2.596l-20.384-13.94z"
								/>
							</g>
						</svg>
					</div>

				</div>

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
						<a
							className="c-gallery-vertical-featured-image__share-icon"
							style={ this.props.listItemStyles.shareButtonStyle }
							onClick={ this.toggleSocialIcons }
							href="/"
						>
							<Share style={ this.props.listItemStyles.shareButtonIconStyle } />
						</a>
					) }

				</div>
			</div>
		);
	}
}

ResponsiveVideoImage.defaultProps = {
	image: '',
	alt: '',
	title: '',
	socialIcons: {},
	socialIconsUseMenu: true,
	twitterUserName: '',
	sizes: {},
	video: '',
};

export default ResponsiveVideoImage;
