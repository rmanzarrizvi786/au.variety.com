/**
 * Creates inline gallery for featured template.
 */

import './../../vendor/slick';

import { debounce } from 'underscore';

const pmcGalleryInline = ( function( $ ) {
	/**
	 * Component.
	 *
	 * @type {Object}
	 */
	const component = {
		/**
		 * Slide settings.
		 *
		 * @type {Object}
		 */
		settings: {
			slidesToShow: 2,
			slidesToScroll: 1,
			infinite: false,
			speed: 200,
			centerMode: true,
			variableWidth: true,
			lazyLoad: 'ondemand',
		},

		/**
		 * Small screen size where the slider scrolling will become regular.
		 *
		 * @type {int}
		 */
		smallScreenSize: 765,

		/**
		 * Sliders.
		 *
		 * @type {Array}
		 */
		sliders: [],
	};

	/**
	 * Initialize.
	 *
	 * @return {Object} Component.
	 */
	component.init = () => {
		component.sliderContainer = $( '.c-gallery-inline__slider' );
		component.galleryInlineContainer = $( '.c-gallery-inline' );

		if ( ! component.galleryInlineContainer.length ) {
			return;
		}

		component.pullSliderToLeftEdgeDebounced = debounce( component.pullSliderToLeftEdge, 300 );
		component.lazyLoadOnSmallScreenDebounced = debounce( component.lazyLoadOnSmallScreen, 100 );

		component.pullSliderToLeftEdge();
		component.createSliders();

		component.bindEvents();

		return component;
	};

	/**
	 * Bind events.
	 *
	 * @return {void}
	 */
	component.bindEvents = () => {
		window.addEventListener( 'resize', component.pullSliderToLeftEdgeDebounced );
		document.addEventListener( 'keydown', component.onKeyDown );
	};

	/**
	 * Pull slider to left edge of the screen.
	 *
	 * @return {void}
	 */
	component.pullSliderToLeftEdge = () => {
		const leftGap = component.galleryInlineContainer.offset().left;

		component.sliderContainer.css( {
			'margin-left': leftGap * -1,
		} );
	};

	/**
	 * Move sliders on key down.
	 *
	 * @param {Object} event Event object.
	 *
	 * @return {void}
	 */
	component.onKeyDown = ( event ) => {
		if ( ! component.sliders.length ) {
			return;
		}

		if ( 'ArrowLeft' === event.key ) {
			component.sliders.forEach( ( slider ) => slider.slick( 'slickPrev' ) );
		} else if ( 'ArrowRight' === event.key ) {
			component.sliders.forEach( ( slider ) => slider.slick( 'slickNext' ) );
		}
	};

	/**
	 * Create sliders.
	 *
	 * @return {void}
	 */
	component.createSliders = () => {
		component.sliderContainer.each( ( index, el ) => {
			const sliderEl = $( el );
			const galleryInlineContainer = sliderEl.closest( '.c-gallery-inline' );
			const counter = galleryInlineContainer.find( '.c-gallery-inline__nav-head' );

			sliderEl.on( 'init', () => {
				galleryInlineContainer.addClass( 'c-gallery-inline__initialized' );
				component.setupSmallScreenSlider( sliderEl );
			} );

			sliderEl.on( 'beforeChange', ( event, slick, currentSlide, nextSlide ) => counter.text( component.padNumberWidthZero( nextSlide + 1 ) ) );

			component.settings.prevArrow = galleryInlineContainer.find( '.c-gallery-inline__nav-left' );
			component.settings.nextArrow = galleryInlineContainer.find( '.c-gallery-inline__nav-right' );

			sliderEl.slick( component.settings );

			component.sliders.push( sliderEl );
		} );
	};

	/**
	 * Setup small screen slider.
	 *
	 * @param {Object} sliderEl Slider node.
	 *
	 * @return {void}
	 */
	component.setupSmallScreenSlider = ( sliderEl ) => {
		if ( window.innerWidth > component.smallScreenSize ) {
			return;
		}

		sliderEl.find( '.slick-list' ).on( 'scroll', () => component.lazyLoadOnSmallScreenDebounced( sliderEl ) );
	};

	/**
	 * Lazy load slides on small screen.
	 *
	 * @param {Object} sliderEl Slide
	 *
	 * @return {void}
	 */
	component.lazyLoadOnSmallScreen = ( sliderEl ) => {
		let imageInView = false;

		sliderEl.find( '.slick-slide' ).each( ( index, el ) => {
			if ( component.isInView( el ) ) {
				imageInView = $( el );
			}
		} );

		if ( false === imageInView ) {
			return;
		}

		const image = imageInView.find( 'img' );

		if ( image.length && '' === image.attr( 'src' ) ) {
			image.attr( 'src', image.data( 'lazy' ) );
		}
	};

	/**
	 * Check if element is in view.
	 *
	 * @param {Object} element Element node.
	 *
	 * @return {boolean} True if in view.
	 */
	component.isInView = ( element ) => {
		const elementIsVisible = element.offsetWidth,
			elementPosition = element.getBoundingClientRect(),
			elementMiddlePosition = ( elementPosition.width / 2 ) + elementPosition.left;

		return ( elementIsVisible > 0 ) && ( elementMiddlePosition >= 0 ) && ( elementMiddlePosition <= window.innerWidth );
	};

	/**
	 * Pad number with zero.
	 *
	 * @param {int} num Slide number.
	 *
	 * @return {string} Padded number.
	 */
	component.padNumberWidthZero = ( num ) => {
		return ( num < 10 ) ? '0' + num : String( num );
	};

	return component;
} )( jQuery );

export default pmcGalleryInline;
