import React from 'react';
import { default as SlickSlider } from 'react-slick';

import { isEmpty, delay } from 'underscore';

import Slide from './slide';
import Arrow from './arrow';
import Interstitial from './interstitial';

import { trackGA } from './../../utils';

/**
 * Slider Component
 */
class Slider extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.onKeyDown = this.onKeyDown.bind( this );
		this.beforeSlideChange = this.beforeSlideChange.bind( this );
		this.afterSlideChange = this.afterSlideChange.bind( this );
		this.onSlideClick = this.onSlideClick.bind( this );
		this.onSwipe = this.onSwipe.bind( this );

		this.eventCategory = ( 'horizontal' === this.props.type ) ? 'standard-gallery' : this.props.type + '-gallery';

		this.slideCount = this.props.slides.length;

		this.settings = {
			speed: 200,
			slidesToShow: 1,
			slidesToScroll: 1,
			centerPadding: 0,
			infinite: false,
			fade: true,
			cssEase: 'linear',
			centerMode: true,
			beforeChange: this.beforeSlideChange,
			afterChange: this.afterSlideChange,
			initialSlide: this.props.initialGalleryIndex,
			swipeEvent: this.onSwipe,
		};
	}

	/**
	 * Change slide when thumbnail index changes.
	 *
	 * @return {void}
	 */
	componentDidUpdate() {
		if ( this.slider ) {
			this.slider.slickGoTo( this.props.galleryIndex );
		}
	}

	/**
	 * When component updates.
	 */
	componentDidMount() {
		document.addEventListener( 'keydown', this.onKeyDown );

		if ( this.slider && this.props.setSliderRef ) {
			this.props.setSliderRef( this.slider );
		}
	}

	/**
	 * When component un-mounts
	 *
	 * @return {void}
	 */
	componentWillUnmount() {
		document.removeEventListener( 'keydown', this.onKeyDown );
	}

	/**
	 * Callback for before slide change.
	 * It has a bit of delay because calling it immediately breaks the slider.
	 *
	 * @link https://github.com/akiran/react-slick/issues/1214
	 *
	 * @param {int} currentIndex Current index.
	 * @param {int} nextIndex    Next slide index.
	 *
	 * @return {void}
	 */
	beforeSlideChange( currentIndex, nextIndex ) {
		const _delay = 10;

		delay( this.props.beforeSlideChange, _delay, currentIndex, nextIndex );
	}

	/**
	 * After slide change.
	 *
	 * @param {int} currentIndex Current index after slide has changed.
	 *
	 * @return {void}
	 */
	afterSlideChange( currentIndex ) {
		this.props.afterSlideChange( currentIndex );
	}

	/**
	 * On Swipe.
	 *
	 * @param {string} direction Swipe direction.
	 *
	 * @return {void}
	 */
	onSwipe( direction ) {
		if ( 'left' === direction ) {
			if ( this.canShowEndSlide() ) {
				this.props.toggleEndSlide( null, true );
			} else if ( this.shouldGoToNextGallery() ) {
				this.goToNextGallery();
			}
		} else if ( 'right' === direction && this.shouldGoToPreviousGallery() ) {
			this.goToPreviousGallery();
		}

		trackGA( {
			hitType: 'event',
			eventCategory: this.eventCategory,
			eventAction: 'swipe',
			eventLabel: `${ direction }-arrow`,
		} );
	}

	/**
	 * Check if the slides have finished and there is next gallery.
	 *
	 * @return {boolean} True if slides finished
	 */
	canShowEndSlide() {
		return ( this.slidesEnded() && this.props.nextGalleryLink && this.props.canShowEndSlide );
	}

	/**
	 * Checks if slides have ended.
	 *
	 * @return {boolean} True if ended.
	 */
	slidesEnded() {
		return ( ( this.props.galleryIndex + 1 ) === this.slideCount );
	}

	/**
	 * Checks if gallery should go to next gallery now.
	 *
	 * @return {boolean} True if should go to next gallery
	 */
	shouldGoToNextGallery() {
		return ( ! this.props.canShowEndSlide && this.slidesEnded() && this.props.nextGalleryLink );
	}

	/**
	 * Go to next gallery.
	 *
	 * @return {void}
	 */
	goToNextGallery() {
		if ( this.shouldGoToNextGallery() ) {
			window.location.href = this.props.nextGalleryLink;
		}
	}

	/**
	 * Checks if should go to previous gallery.
	 *
	 * @return {boolean} True is should go to next gallery.
	 */
	shouldGoToPreviousGallery() {
		return ( 0 === this.props.galleryIndex && this.props.previousGalleryLink );
	}

	/**
	 * Go to previous gallery.
	 *
	 * @return {void}
	 */
	goToPreviousGallery() {
		if ( this.shouldGoToPreviousGallery() ) {
			window.location.href = this.props.previousGalleryLink;
		}
	}

	/**
	 * On slide image click, change slider to next.
	 *
	 * @param {Object} event Click event object.
	 *
	 * @return {void}
	 */
	onSlideClick( event ) {
		event.preventDefault();
		this.slider.slickNext();
	}

	/**
	 * On keydown, for next and previous navigation.
	 *
	 * @param {Object} event keydown event.
	 *
	 * @return {void}
	 */
	onKeyDown( event ) {
		let eventLabel = null;

		if ( ! this.slider || this.props.navigationLocked ) {
			return;
		}

		if ( 'ArrowLeft' === event.key ) {
			this.props.toggleEndSlide( null, false );

			if ( this.props.showInterstitial ) {
				this.props.toggleInterstitial( false );
			} else if ( this.shouldGoToPreviousGallery() ) {
				this.goToPreviousGallery();
			} else {
				this.slider.slickPrev();
				eventLabel = 'left-arrow';
			}
		} else if ( 'ArrowRight' === event.key ) {
			if ( this.props.showInterstitial ) {
				this.props.toggleInterstitial( false );
			} else if ( this.canShowEndSlide() ) {
				this.props.toggleEndSlide( null, true );
			} else if ( this.shouldGoToNextGallery() ) {
				this.goToNextGallery();
			} else {
				this.slider.slickNext();
				eventLabel = 'right-arrow';
			}
		}

		if ( eventLabel ) {
			trackGA( {
				hitType: 'event',
				eventCategory: this.eventCategory,
				eventAction: 'key-press',
				eventLabel,
			} );
		}
	}

	/**
	 * Render component.
	 *
	 * @return {*} Component object.
	 */
	render() {
		const {
			slides,
			showInterstitial,
			canLoadInterstitialAd,
			navigationLocked,
			advert,
			adsProvider,
			i10n,
			galleryIndex,
			initialGalleryIndex,
			slideIndexesToLoad,
			canLoadAds,
			pinit,
			classes,
			magnifyImage,
			prevArrow,
			isMediumSize,
			nextArrow,
			nextGalleryTitle,
			nextGalleryLink,
			nextGalleryType,
			previousGalleryLink,
		} = this.props;

		const showInterstitialClass = showInterstitial ? 'c-gallery-slide-interstitial c-gallery-slide-interstitial--active' : 'c-gallery-slide-interstitial';
		const nextGallery = {
			title: nextGalleryTitle,
			link: nextGalleryLink,
			type: nextGalleryType,
		};

		if ( isEmpty( slides ) ) {
			return null;
		}

		this.settings.prevArrow = prevArrow || (
			<Arrow
				to="prev"
				arrowClass="u-gallery-arrow u-gallery-arrow--prev"
				previousGalleryLink={ previousGalleryLink }
				galleryIndex={ galleryIndex }
				slideCount={ this.slideCount }
			/>
		);

		this.settings.nextArrow = nextArrow || (
			<Arrow
				to="next"
				arrowClass="u-gallery-arrow u-gallery-arrow--next"
				nextGallery={ nextGallery }
				galleryIndex={ galleryIndex }
				slideCount={ this.slideCount }
				toggleEndSlide={ this.props.toggleEndSlide }
				canShowEndSlide={ this.props.canShowEndSlide }
			/>
		);

		return (
			<div className="c-gallery-slider">
				{ 1 === this.slideCount && ( // Slick will not show arrows if there is only one slide.
					this.settings.prevArrow
				) }
				<SlickSlider ref={ slider => ( this.slider = slider ) } { ...this.settings }>
					{ slides && slides.map( ( slide, index ) => {
						return <Slide
							onSlideClick={ this.onSlideClick }
							key={ index }
							slideIndexesToLoad={ slideIndexesToLoad }
							initialGalleryIndex={ initialGalleryIndex }
							slideIndex={ index }
							pinit={ pinit }
							classes={ classes }
							isMediumSize={ isMediumSize }
							magnifyImage={ magnifyImage }
							{ ...slide }
						/>;
					} ) }
				</SlickSlider>
				<div className={ showInterstitialClass } >
					{ canLoadAds && <Interstitial
						advert={ advert }
						adsProvider={ adsProvider }
						i10n={ i10n }
						toggleInterstitial={ this.props.toggleInterstitial }
						canLoadInterstitialAd={ canLoadInterstitialAd }
						showInterstitial={ showInterstitial }
						navigationLocked={ navigationLocked }
						toggleNavigationLock={ this.props.toggleNavigationLock }
					/> }
				</div>
				{ 1 === this.slideCount && (
					this.settings.nextArrow
				) }
			</div>
		);
	}
}

Slide.defaultProps = {
	slides: [],
	i10n: {},
	pinit: true,
	isMediumSize: false,
	navigationLocked: false,
	classes: {
		figure: 'c-gallery-slide',
		img: 'c-gallery-slide__image',
	},
	nextGalleryLink: '',
	nextGalleryTitle: '',
	nextGalleryType: '',
	previousGalleryLink: '',
};

export default Slider;
