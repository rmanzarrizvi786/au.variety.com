/* globals window */

import React from 'react';

import withGallery from './../with-gallery';
import Slider from './../slider';
import Thumbnails from './thumbnails';
import SidebarRight from './sidebar-right';
import SidebarLeft from './sidebar-left';
import IntroCard from './../intro-card';
import EndSlide from './../endslide';
import MagnifyingGlass from './../svg/magnifying-glass';
import ZoomModal from './../zoom-modal';
import ThumbnailsModal from './thumbnails/thumbnails-modal';
import Arrow from '../slider/arrow';
import MobileHeader from './mobile-header';
import MobileFooter from './mobile-footer';

import { isEmpty, isFunction } from 'underscore';

import { trackGA } from './../../utils';
import Menu from './sidebar-left/menu';
import { CSSTransitionGroup } from 'react-transition-group';
import PropTypes from 'prop-types';

/**
 * Gallery Component.
 */
class GalleryRunway extends React.Component {
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
		this.setSliderRef = this.setSliderRef.bind( this );
		this.moveToNextSlide = this.moveToNextSlide.bind( this );
		this.moveToPreviousSlide = this.moveToPreviousSlide.bind( this );
		this.toggleMobileMenu = this.toggleMobileMenu.bind( this );
		this.onEscape = this.onEscape.bind( this );

		this.closeButtonLink = window.location.href !== this.props.closeButtonLink ? this.props.closeButtonLink : '';

		this.slider = {};

		this.state = {
			isThumbnailsActive: false,
			displayEndSlide: false,
			displayIntroCard: ( 0 === this.props.galleryIndex && ! isEmpty( this.props.introCard ) ),
			showMobileMenu: false,
		};
	}

	/**
	 * When component mounts.
	 *
	 * @return {void}
	 */
	componentDidMount() {
		document.addEventListener( 'keydown', this.onEscape, true );
	}

	/**
	 * When component un-mounts.
	 *
	 * @return {void}
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

			if ( this.state.isThumbnailsActive ) {
				this.toggleThumbnailActiveState( event );
			}
		}
	}

	/**
	 * Set slider ref.
	 *
	 * @param {Object} slider Slider ref from slider component.
	 *
	 * @return {void}
	 */
	setSliderRef( slider ) {
		this.slider = slider;
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
				eventCategory: 'runway-gallery',
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
	 * Toggle mobile menu.
	 *
	 * @param {Object} event Click event object.
	 *
	 * @return {void}
	 */
	toggleMobileMenu( event ) {
		event.preventDefault();

		this.setState( {
			showMobileMenu: ! this.state.showMobileMenu,
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
				eventCategory: 'runway-gallery',
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
	 * Move to next slide.
	 *
	 * @param {Object} event Event object.
	 *
	 * @return {void}
	 */
	moveToNextSlide( event ) {
		event.preventDefault();

		if ( this.slider && isFunction( this.slider.slickNext ) ) {
			this.slider.slickNext();
		}
	}

	/**
	 * Move to previous slide.
	 *
	 * @param {Object} event Event object.
	 *
	 * @return {void}
	 */
	moveToPreviousSlide( event ) {
		event.preventDefault();

		if ( this.slider && isFunction( this.slider.slickPrev ) ) {
			this.slider.slickPrev();
		}
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
			gallery,
			type,
			galleryIndex,
			initialGalleryIndex,
			location,
			ads,
			adsProvider,
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
			isMediumSize,
			timestamp,
			forceSameEnding,
			runwayMenu,
			subscriptionsLink,
			showInterstitial,
			canLoadInterstitialAd,
			navigationLocked,
			galleryId,
			galleryFetchUrl,
			nextGalleryLink,
			nextGalleryTitle,
			nextGalleryType,
		} = this.props;

		const titleClass = isEmpty( sponsored ) ? 'c-gallery-sidebar__slide-title' : 'c-gallery-sidebar__slide-title c-gallery-header__title-with-sponsored-text';

		const currentSlide = this.props.getCurrentSlide();
		const { title, caption, sizes, alt, pinterestUrl } = currentSlide;

		const imageCredit = currentSlide.image_credit;
		const canShowEndSlide = forceSameEnding;

		const nextGallery = {
			title: nextGalleryTitle,
			link: nextGalleryLink,
		};

		const galleryClasses = [ 'c-gallery-runway' ];

		const prevArrow = <Arrow
			to="prev"
			arrowClass="u-gallery-arrow u-gallery-arrow--prev"
			previousGalleryLink={ previousGalleryLink }
			galleryIndex={ galleryIndex }
			slideCount={ gallery.length }
			onExternalArrowClick={ this.moveToPreviousSlide }
		/>;

		const nextArrow = <Arrow
			to="next"
			arrowClass="u-gallery-arrow u-gallery-arrow--next"
			nextGallery={ nextGallery }
			galleryIndex={ galleryIndex }
			slideCount={ gallery.length }
			toggleEndSlide={ this.toggleEndSlide }
			canShowEndSlide={ canShowEndSlide }
			onExternalArrowClick={ this.moveToNextSlide }
		/>;

		const magnifyImage = ! isMediumSize;

		if ( this.state.isThumbnailsActive ) {
			galleryClasses.push( 'c-gallery--thumbnails-active' );
		}

		if ( this.state.displayEndSlide ) {
			galleryClasses.push( 'c-gallery--end-slide-active' );
		}

		return (
			<div className={ galleryClasses.join( ' ' ) } >
				{ isMediumSize && (
					<MobileHeader
						siteUrl={ siteUrl }
						siteTitle={ siteTitle }
						logo={ logo }
						i10n={ i10n }
						galleryIndex={ galleryIndex }
						totalCount={ gallery.length }
						closeButtonLink={ this.closeButtonLink }
						toggleThumbnailActiveState={ this.toggleThumbnailActiveState }
					/>
				) }
				<SidebarLeft
					siteTitle={ siteTitle }
					siteUrl={ siteUrl }
					logo={ logo }
					closeButtonLink={ this.closeButtonLink }
					i10n={ i10n }
					galleryIndex={ galleryIndex }
					totalCount={ gallery.length }
					subscriptionsLink={ subscriptionsLink }
					prevArrow={ prevArrow }
					nextArrow={ nextArrow }
					runwayMenu={ runwayMenu }
					isMediumSize={ isMediumSize }
					galleryTitle={ galleryTitle }
					imageCredit={ imageCredit }
				/>
				<div className="c-gallery-runway__slider">
					{ zoom && ! isMediumSize && (
						<a onClick={ this.props.toggleZoomModal } className="c-gallery__image-zoom" href="/">
							<MagnifyingGlass color={ styles[ 'theme-color' ] } />
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
						toggleNavigationLock={ this.props.toggleNavigationLock }
						canLoadInterstitialAd={ canLoadInterstitialAd }
						navigationLocked={ navigationLocked }
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
						prevArrow={ prevArrow }
						nextArrow={ nextArrow }
						setSliderRef={ this.setSliderRef }
						isMediumSize={ isMediumSize }
						magnifyImage={ magnifyImage }
						galleryId={ galleryId }
						type={ type }
						galleryFetchUrl={ galleryFetchUrl }
						classes={ {
							figure: 'c-gallery-runway-slide',
							img: 'c-gallery-runway-slide__image',
						} }
					/>

					{ isMediumSize && this.state.showMobileMenu && (
						<nav className="c-gallery-runway-nav c-gallery-runway-mobile-footer__nav" >
							<Menu
								menu={ runwayMenu }
							/>
						</nav>
					) }

				</div>
				{ isMediumSize && (
					<MobileFooter
						subscriptionsLink={ subscriptionsLink }
						i10n={ i10n }
						toggleMobileMenu={ this.toggleMobileMenu }
						titleClass={ titleClass }
						galleryTitle={ galleryTitle }
					/>
				) }
				<Thumbnails
					thumbnails={ gallery }
					i10n={ i10n }
					updateGalleryIndex={ this.props.updateGalleryIndex }
					galleryIndex={ galleryIndex }
					toggleThumbnailActiveState={ this.toggleThumbnailActiveState }
					navigationLocked={ navigationLocked }
				/>
				<SidebarRight
					title={ title }
					caption={ caption }
					advert={ ads.rightRailGallery }
					adsProvider={ adsProvider }
					canLoadAds={ canLoadAds }
					timestamp={ timestamp }
					isMediumSize={ isMediumSize }
					galleryTitle={ galleryTitle }
					sponsored={ sponsored }
					sponsoredStyle={ sponsoredStyle }
					socialIcons={ socialIcons }
					twitterUserName={ twitterUserName }
					pinterestUrl={ pinterestUrl }
					location={ location }
					type={ type }
				/>
				<CSSTransitionGroup transitionName="c-gallery-zoom-modal__animation-fade" transitionEnterTimeout={ 200 } transitionLeaveTimeout={ 400 } >
					{ this.state.isThumbnailsActive && (
						<ThumbnailsModal
							thumbnails={ gallery }
							i10n={ i10n }
							updateGalleryIndex={ this.props.updateGalleryIndex }
							galleryIndex={ galleryIndex }
							toggleThumbnailActiveState={ this.toggleThumbnailActiveState }
							nextGallery={ nextGallery }
						/>
					) }
				</CSSTransitionGroup>
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

GalleryRunway.defaultProps = {
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
	nextGallery: {},
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
	runwayMenu: {},
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
	galleryId: 0,
	galleryFetchUrl: '',
	previousGalleryLink: '',
	nextGalleryLink: '',
	nextGalleryTitle: '',
	nextGalleryType: '',
};

GalleryRunway.propTypes = {
	gallery: PropTypes.array.isRequired,
	galleryTitle: PropTypes.string,
	nextGallery: PropTypes.object,
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
	runwayMenu: PropTypes.object,
	isMediumSize: PropTypes.bool,
	introCard: PropTypes.object,
	sponsored: PropTypes.string,
	sponsoredStyle: PropTypes.object,
	forceSameEnding: PropTypes.bool,
	ads: PropTypes.object,
	socialIcons: PropTypes.object,
	twitterUserName: PropTypes.string,
	subscriptionsLink: PropTypes.string,
	galleryId: PropTypes.number,
	galleryFetchUrl: PropTypes.string,
	previousGalleryLink: PropTypes.string,
	nextGalleryLink: PropTypes.string,
	nextGalleryTitle: PropTypes.string,
	nextGalleryType: PropTypes.string,
};

export { GalleryRunway };
export default withGallery( GalleryRunway );
