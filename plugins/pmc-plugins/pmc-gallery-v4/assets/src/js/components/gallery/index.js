/* globals window */

import React from 'react';
import PropTypes from 'prop-types';

import { isEmpty } from 'underscore';

import Slider from './../slider';
import Thumbnails from './thumbnails';
import Sidebar from './../sidebar';
import Header from './header';
import withGallery from './../with-gallery';
import IntroCard from './../intro-card';
import EndSlide from './../endslide';
import ThumbnailCounter from './../thumbnail-counter';
import MagnifyingGlass from './../svg/magnifying-glass';
import Zoom from './../svg/zoom';
import ZoomModal from './../zoom-modal';
import SocialIcons from './../social-icons';
import Advert from './../advert';

import { trackGA } from './../../utils';

import { CSSTransitionGroup } from 'react-transition-group';

/**
 * Gallery Component.
 */
class Gallery extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.toggleThumbnailActiveState = this.toggleThumbnailActiveState.bind( this );
		this.toggleEndSlide = this.toggleEndSlide.bind( this );
		this.closeIntroCard = this.closeIntroCard.bind( this );
		this.onEscape = this.onEscape.bind( this );
		this.closeButtonLink = window.location.href !== this.props.closeButtonLink ? this.props.closeButtonLink : '';

		this.state = {
			isThumbnailsActive: false,
			displayEndSlide: false,
			displayIntroCard: ( 0 === this.props.galleryIndex && ! isEmpty( this.props.introCard ) ),
		};
	}

	/**
	 * When component mounts.
	 */
	componentDidMount() {
		document.addEventListener( 'keydown', this.onEscape );
	}

	/**
	 * When component unmounts.
	 */
	componentWillUnmount() {
		document.removeEventListener( 'keydown', this.onEscape );
	}

	/**
	 * On esc key press.
	 *
	 * @param {object} event Key down event.
	 *
	 * @return {void}
	 */
	onEscape( event ) {
		if ( 'Escape' === event.key ) {
			this.closeIntroCard();
			this.toggleEndSlide( event, false );
		}
	}

	/**
	 * Toggle thumbnail active state.
	 *
	 * @param {Object} event Click event object.
	 *
	 * @return {void}
	 */
	toggleThumbnailActiveState( event ) {
		event.preventDefault();

		this.setState( ( state ) => {
			const isThumbnailsActive = ! state.isThumbnailsActive;
			const eventLabel = isThumbnailsActive ? 'lightbox-open' : 'lightbox-close';

			trackGA( {
				hitType: 'event',
				eventCategory: 'standard-gallery',
				eventAction: 'click',
				eventLabel,
				nonInteraction: true,
			} );

			return {
				isThumbnailsActive,
			};
		} );
	}

	/**
	 * Toggle EndSlide.
	 *
	 * @param {Object}  event  Click event object.
	 * @param {Boolean} status True for displaying and false for hide.
	 *
	 * @return {void}
	 */
	toggleEndSlide( event, status ) {
		if ( event ) {
			event.preventDefault();
		}

		this.setState( {
			displayEndSlide: undefined !== status ? status : ! this.state.displayEndSlide,
		} );
	}

	/**
	 * Close intro card.
	 *
	 * @param {Object} event Click event object.
	 *
	 * @return {void}
	 */
	closeIntroCard( event ) {
		if ( event ) {
			event.preventDefault();
		}

		this.setState( () => {
			const displayIntroCard = false;

			trackGA( {
				hitType: 'event',
				eventCategory: 'standard-gallery',
				eventAction: 'click',
				eventLabel: 'gallery-intro-close',
				nonInteraction: true,
			} );

			return {
				displayIntroCard,
			};
		} );
	}

	/**
	 * Render Component.
	 *
	 * @return {*} Component object.
	 */
	render() {
		const {
			siteTitle,
			galleryTitle,
			logo,
			siteUrl,
			i10n,
			type,
			gallery,
			galleryIndex,
			initialGalleryIndex,
			location,
			ads,
			previousGalleryLink,
			slideIndexesToLoad,
			canLoadAds,
			zoom,
			pinit,
			showZoomImageModal,
			styles,
			introCard,
			sponsored,
			sponsoredStyle,
			socialIcons,
			twitterUserName,
			showThumbnails,
			adsProvider,
			isMediumSize,
			timestamp,
			forceSameEnding,
			subscriptionsLink,
			showInterstitial,
			navigationLocked,
			canLoadInterstitialAd,
			isMobile,
			mobileCloseButton,
			galleryId,
			galleryFetchUrl,
			nextGalleryLink,
			nextGalleryTitle,
			nextGalleryType,
		} = this.props;

		const { title, caption, sizes, alt, pinterestUrl } = this.props.getCurrentSlide();
		const imageCredit = this.props.getCurrentSlide().image_credit;
		const canShowEndSlide = forceSameEnding;

		const nextGallery = {
			title: nextGalleryTitle,
			link: nextGalleryLink,
		};

		const galleryClasses = [ 'c-gallery' ];

		if ( this.state.isThumbnailsActive ) {
			galleryClasses.push( 'c-gallery--thumbnails-active' );
		}

		if ( this.state.displayEndSlide ) {
			galleryClasses.push( 'c-gallery--end-slide-active' );
		}

		const leaderBoardAd = (
			<div className="c-gallery__header-leader-board-ad">
				<Advert
					advert={ ads.headerLeaderBoard || '' }
					adsProvider={ adsProvider }
				/>
			</div>
		);

		return (
			<div id="gallery-container" className={ galleryClasses.join( ' ' ) } >
				<header id="gallery-header" className="c-gallery__header">
					<Header
						siteTitle={ siteTitle }
						galleryTitle={ galleryTitle }
						logo={ logo }
						siteUrl={ siteUrl }
						i10n={ i10n }
						slideTitle={ title }
						closeButtonLink={ this.closeButtonLink }
						location={ location }
						socialIcons={ socialIcons }
						twitterUserName={ twitterUserName }
						isMediumSize={ isMediumSize }
						totalCount={ gallery.length }
						currentCount={ galleryIndex + 1 }
						thumbnailsActive={ this.state.isThumbnailsActive }
						toggleThumbnailActiveState={ this.toggleThumbnailActiveState }
						isThumbnailsActive={ this.state.isThumbnailsActive }
						showThumbnails={ showThumbnails }
						sponsored={ sponsored }
						sponsoredStyle={ sponsoredStyle }
						styles={ styles }
						mobileCloseButton={ mobileCloseButton }
					/>
					{ isMobile && ads.headerLeaderBoard && leaderBoardAd }
				</header>

				<main className="c-gallery__main" id="pagetop">
					{ showThumbnails && ! isMediumSize && (
						<ThumbnailCounter
							i10n={ i10n }
							totalCount={ gallery.length }
							currentCount={ galleryIndex + 1 }
							thumbnailsActive={ this.state.isThumbnailsActive }
							toggleThumbnailActiveState={ this.toggleThumbnailActiveState }
							isThumbnailsActive={ this.state.isThumbnailsActive }
						/>
					) }
					{ showThumbnails && (
						<div className="c-gallery__thumbnails c-galley-thumbnails">
							<Thumbnails
								thumbnails={ gallery }
								i10n={ i10n }
								updateGalleryIndex={ this.props.updateGalleryIndex }
								galleryIndex={ galleryIndex }
								isMediumSize={ isMediumSize }
								navigationLocked={ navigationLocked }
								toggleThumbnailActiveState={ this.toggleThumbnailActiveState }
							/>
						</div>
					) }
					<div className="c-gallery__slider">
						{ zoom && (
							<a onClick={ this.props.toggleZoomModal } className="c-gallery__image-zoom" href="/">
								{ 'runway' === type && (
									<MagnifyingGlass color={ styles[ 'theme-color' ] } />
								) }
								{ 'runway' !== type && (
									<Zoom />
								) }
							</a>
						) }
						<Slider
							slides={ gallery }
							i10n={ i10n }
							beforeSlideChange={ this.props.beforeSlideChange }
							afterSlideChange={ this.props.afterSlideChange }
							galleryIndex={ galleryIndex }
							isThumbnailsActive={ this.state.isThumbnailsActive }
							initialGalleryIndex={ initialGalleryIndex }
							showInterstitial={ showInterstitial }
							navigationLocked={ navigationLocked }
							toggleNavigationLock={ this.props.toggleNavigationLock }
							canLoadInterstitialAd={ canLoadInterstitialAd }
							advert={ ads.galleryInterstitial }
							adsProvider={ adsProvider }
							toggleInterstitial={ this.props.toggleInterstitial }
							toggleEndSlide={ this.toggleEndSlide }
							canShowEndSlide={ canShowEndSlide }
							previousGalleryLink={ previousGalleryLink }
							nextGalleryLink={ nextGalleryLink }
							nextGalleryTitle={ nextGalleryTitle }
							nextGalleryType={ nextGalleryType }
							slideIndexesToLoad={ slideIndexesToLoad }
							canLoadAds={ canLoadAds }
							pinit={ pinit }
							type={ type }
							galleryId={ galleryId }
							galleryFetchUrl={ galleryFetchUrl }
						/>
					</div>
					<aside className="c-gallery__sidebar">
						<Sidebar
							title={ title }
							caption={ caption }
							imageCredit={ imageCredit }
							advert={ ads.rightRailGallery }
							galleryMobileBottomAdvert={ ads.galleryMobileBottom || '' }
							adsProvider={ adsProvider }
							canLoadAds={ canLoadAds }
							timestamp={ timestamp }
							isMediumSize={ isMediumSize }
							galleryTitle={ galleryTitle }
							sponsored={ sponsored }
							sponsoredStyle={ sponsoredStyle }
							styles={ styles }
							isMobile={ isMobile }
						/>
					</aside>
					{ this.state.displayIntroCard && ! isEmpty( introCard ) && (
						<div className="c-gallery__intro-card">
							<IntroCard
								{ ...introCard }
								galleryTitle={ galleryTitle }
								i10n={ i10n }
								closeIntroCard={ this.closeIntroCard }
								twitterUserName={ twitterUserName }
								isMediumSize={ isMediumSize }
							/>
						</div>
					) }
					{ canShowEndSlide && (
						<EndSlide
							i10n={ i10n }
							subscriptionsLink={ subscriptionsLink }
							toggleEndSlide={ this.toggleEndSlide }
							displayEndSlide={ this.state.displayEndSlide }
							nextGallery={ nextGallery }
						/>
					) }
				</main>
				{ isMediumSize && (
					<footer className="c-gallery__footer c-gallery-header" >
						<SocialIcons
							location={ location }
							slideTitle={ title }
							socialIcons={ socialIcons }
							twitterUserName={ twitterUserName }
							pinterestUrl={ pinterestUrl }
							linkClassPrefix="c-gallery-social-icons__icon"
							ulClassName="c-gallery-social-icons"
							liClassName="c-gallery-social-icons__icon"
						/>
					</footer>
				) }
				{ zoom && (
					<CSSTransitionGroup transitionName="c-gallery-zoom-modal__animation-fade" transitionEnterTimeout={ 200 } transitionLeaveTimeout={ 400 } >
						{ showZoomImageModal && (
							<ZoomModal
								image={ sizes[ 'pmc-gallery-xxl' ] }
								toggleZoomModal={ this.props.toggleZoomModal }
								alt={ alt }
							/>
						) }
					</CSSTransitionGroup>
				) }
			</div>
		);
	}
}

Gallery.defaultProps = {
	gallery: [
		{
			ID: 0,
			caption: '',
			date: '',
			description: '',
			image: '',
			image_credit: '',
			slug: '',
			modified: '',
			title: '',
			url: '',
		},
	],
	galleryTitle: '',
	galleryIndex: 0,
	initialGalleryIndex: 0,
	pagePermalink: '',
	logo: {},
	siteTitle: '',
	siteUrl: '',
	i10n: {},
	timestamp: {},
	styles: {},
	showThumbnails: true,
	linkToAllGalleries: '',
	closeButtonLink: '',
	zoom: true,
	pinit: true,
	isMediumSize: false,
	introCard: {},
	sponsored: '',
	sponsoredStyle: {},
	forceSameEnding: false,
	ads: {
		rightRailGallery: {},
		galleryInterstitial: {},
	},
	socialIcons: {},
	twitterUserName: '',
	subscriptionsLink: '',
	isMobile: false,
	mobileCloseButton: '',
	galleryId: 0,
	galleryFetchUrl: '',
	previousGalleryLink: '',
	nextGalleryLink: '',
	nextGalleryTitle: '',
	nextGalleryType: '',
};

Gallery.propTypes = {
	gallery: PropTypes.array.isRequired,
	galleryTitle: PropTypes.string,
	galleryIndex: PropTypes.number,
	initialGalleryIndex: PropTypes.number,
	pagePermalink: PropTypes.string,
	logo: PropTypes.object,
	siteTitle: PropTypes.string,
	siteUrl: PropTypes.string,
	i10n: PropTypes.object,
	timestamp: PropTypes.object,
	styles: PropTypes.object,
	showThumbnails: PropTypes.bool,
	linkToAllGalleries: PropTypes.string,
	closeButtonLink: PropTypes.string,
	zoom: PropTypes.bool,
	pinit: PropTypes.bool,
	isMediumSize: PropTypes.bool,
	introCard: PropTypes.object,
	sponsored: PropTypes.string,
	sponsoredStyle: PropTypes.object,
	forceSameEnding: PropTypes.bool,
	ads: PropTypes.object,
	socialIcons: PropTypes.object,
	twitterUserName: PropTypes.string,
	subscriptionsLink: PropTypes.string,
	isMobile: PropTypes.bool,
	mobileCloseButton: PropTypes.string,
	galleryId: PropTypes.number,
	galleryFetchUrl: PropTypes.string,
	previousGalleryLink: PropTypes.string,
	nextGalleryLink: PropTypes.string,
	nextGalleryTitle: PropTypes.string,
	nextGalleryType: PropTypes.string,
};

export { Gallery };
export default withGallery( Gallery );
