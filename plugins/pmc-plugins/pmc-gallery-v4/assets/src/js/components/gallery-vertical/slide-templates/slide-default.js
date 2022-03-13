import React, { Fragment } from 'react';
import Share from './../../svg/share';
import MagnifyingGlassLight from './../../svg/magnifying-glass-light';
import DOMPurify from 'dompurify';
import SocialIcons from './../../social-icons';
import ResponsiveImage from '../../responsive-image';
import { CSSTransitionGroup } from 'react-transition-group';

DOMPurify.setConfig( { ADD_ATTR: [ 'target' ] } );

class SlideDefault extends React.Component {
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
		const { ID, alt, caption, title, i10n, sizes, pinterestUrl, slideIndex, socialIcons, socialIconsUseMenu, twitterUserName, location } = this.props;
		const imageCredit = this.props.image_credit; // eslint-disable-line
		const imageSourceUrl = this.props.image_source_url; // eslint-disable-line
		const innerImageCredit = (
			<span className="c-gallery-vertical-slide__photo-credit">{ imageCredit }</span>
		);

		return (
			<div className="c-gallery-vertical-slide">
				<div className="c-gallery-vertical-slide__image-container">
					<ResponsiveImage
						fullWidth={ this.props.fullWidth }
						fullHeight={ this.props.fullHeight }
						sizes={ sizes }
						onFigureClick={ () => {} }
						alt={ alt }
						ID={ ID }
						classes={ {
							figure: 'c-gallery-vertical-slide__figure',
							img: 'c-gallery-vertical-slide__image u-gallery-react-placeholder-shimmer',
						} }
					>
						<a className="c-gallery-vertical-slide__zoom-icon u-gallery-icon-zoom" onClick={ ( event ) => this.props.toggleZoomModal( event, null, slideIndex ) } href="/" >
							<MagnifyingGlassLight />
						</a>

						<div className="c-gallery-vertical-slide__share-icons">
							<CSSTransitionGroup transitionName="c-gallery-vertical-share__animation" transitionEnterTimeout={ 500 } transitionLeaveTimeout={ 500 } >
								{ this.state.displaySocialIcons && (
									<SocialIcons
										socialIcons={ socialIcons }
										twitterUserName={ twitterUserName }
										location={ location }
										slideTitle={ title }
										pinterestUrl={ pinterestUrl }
										linkClassPrefix="u-gallery-social-icon u-gallery"
										liClassName="c-gallery-vertical-slide__social-icon"
										ulClassName="c-gallery-vertical-slide__social-icons"
									/>
								) }
							</CSSTransitionGroup>
							{ socialIconsUseMenu && (
								<a onClick={ this.toggleSocialIcons } href="/" className="c-gallery-vertical-slide__share-icon">
									<Share />
								</a>
							) }
						</div>
					</ResponsiveImage>
				</div>
				{ imageCredit && (
					<div className="c-gallery-vertical-slide__photo-credit-wrapper">
						<Fragment>
							<span className="c-gallery-vertical-slide__photo-credit-text">{ i10n.vertical.photo }</span>
							<span className="c-gallery-vertical-slide__colon"> : </span>
							{ imageSourceUrl ? ( <a href={ imageSourceUrl } target="_blank" rel="noopener noreferrer" className="c-gallery-vertical-slide__image-source-url">{ innerImageCredit }</a> ) : innerImageCredit }
						</Fragment>
					</div>
				) }
				<h2 className="c-gallery-vertical-slide__title" dangerouslySetInnerHTML={ {
					__html: DOMPurify.sanitize( title ),
				} } />
				<div className="c-gallery-vertical-slide__caption" dangerouslySetInnerHTML={ {
					__html: DOMPurify.sanitize( caption ),
				} } />
			</div>
		);
	}
}

SlideDefault.defaultProps = {
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
	slideIndex: 0,
	sizes: {},
	ordering: '',
};

export default SlideDefault;
