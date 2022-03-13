/* globals window, IntersectionObserver */

import React from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

import Slide from './slide';
import ErrorBoundary from '../error-boundary';
import ListNavBar from './listNavBar.js';
import Advert from './../advert';
import ZoomModal from './../zoom-modal';

import {
	debounce,
	each,
	findLastIndex,
	first,
	isEmpty,
	isNumber,
	last,
	map,
} from 'underscore';

import {
	maybeTriggerAdRefresh,
	fixPathname,
	lazyLoadAds,
	removeCustomNextGalleryURLParam,
	trackDimensions,
	trackGA,
	trackUrlHashChange,
	updateOldHashToNewSlug,
} from './../../utils';

import { CSSTransitionGroup } from 'react-transition-group';

class GalleryVertical extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.onScrollDebounced = debounce( this.onScroll.bind( this ), 20 );
		this.updateSlugDebounced = debounce( this.updateSlug.bind( this ), 50 );
		this.sendTrackingDebounced = debounce( this.sendTracking, 300 );

		this.maybeScrollToSlide = this.maybeScrollToSlide.bind( this );
		this.toggleZoomModal = this.toggleZoomModal.bind( this );
		this.onEscape = this.onEscape.bind( this );
		this.onPopState = this.onPopState.bind( this );
		this.getSlideIndexByID = this.getSlideIndexByID.bind( this );
		this.getSlideByID = this.getSlideByID.bind( this );
		this.onIntersect = this.onIntersect.bind( this );

		removeCustomNextGalleryURLParam();

		this.initialSlug = fixPathname( window.location.pathname ).split( this.props.pagePermalink )[ 1 ];
		this.isComponentReady = false;
		this.firstSlideOffsetY = 0;

		const gallery = this.props.gallery;
		this.slideRefs = {};

		this.state = {
			showZoomImageModal: false,
			clickedImageIndex: 0,
		};

		if ( isEmpty( gallery ) ) {
			return;
		}

		this.totalSlides = gallery.length;
		this.viewedSlideIDs = [];

		updateOldHashToNewSlug( gallery );

		gallery.forEach( ( slide ) => {
			this.slideRefs[ slide.ID ] = React.createRef();
		} );
	}

	/**
	 * When component mounts.
	 *
	 * @return {void}
	 */
	componentDidMount() {
		document.addEventListener( 'keydown', this.onEscape );
		window.addEventListener( 'popstate', this.onPopState );

		this.maybeScrollToSlide( this.initialSlug );

		this.observer = this.createObserver();

		lazyLoadAds();

		this.prepareListNavBarContainer();
		this.renderListNavBar();

		this.isComponentReady = true;
	}

	/**
	 * When component un-mounts.
	 *
	 * @return {void}
	 */
	componentWillUnmount() {
		document.removeEventListener( 'keydown', this.onEscape );
		window.removeEventListener( 'popstate', this.onPopState );

		if ( this.observer ) {
			this.observer.disconnect();
		} else {
			document.removeEventListener( 'scroll', this.onScrollDebounced );
		}
	}

	/**
	 * Create intersection observer
	 *
	 * @return {IntersectionObserver|null} observer instance or null
	 */
	createObserver() {
		const options = {
			root: null,
			rootMargin: '0px',
			threshold: [ 0.5 ],
		};

		if ( ! ( 'IntersectionObserver' in window ) || ! ( 'IntersectionObserverEntry' in window ) || ! ( 'intersectionRatio' in window.IntersectionObserverEntry.prototype ) ) {
			// Fallback when intersection observer api is not available.
			document.addEventListener( 'scroll', this.onScrollDebounced );

			return null;
		}

		const observer = new IntersectionObserver( this.onIntersect, options );

		each( this.slideRefs, ( ref ) => {
			const slide = ref.current;

			if ( slide ) {
				observer.observe( slide );
			}
		} );

		return observer;
	}

	/**
	 * Intersection callback.
	 *
	 * @param {object} entries Intersection entries.
	 *
	 * @return {void}
	 */
	onIntersect( entries ) {
		const intersectingEntry = entries.find( entry => entry.isIntersecting );
		const minimumScrollTop = 200;

		// When user reaches top or when the user is already at top.
		if ( window.scrollY <= minimumScrollTop ) {
			this.removeSlug();
			return;
		}

		if ( ! intersectingEntry || ! intersectingEntry.target ) {
			return;
		}

		const slideID = intersectingEntry.target.getAttribute( 'data-slide-id' );

		this.renderListNavBar( intersectingEntry.target.dataset.slideIndex );

		this.updateSlugDebounced( slideID );
	}

	/**
	 * On page scroll as fallback.
	 *
	 * @return {void}
	 */
	onScroll() {
		let slideIsInView = false;

		// Bail out if scrolling starts before the component is ready.
		if ( ! this.isComponentReady ) {
			return;
		}

		if ( isEmpty( this.slideRefs ) ) {
			return;
		}

		each( this.slideRefs, ( ref, ID ) => {
			if ( this.constructor.isNodeInView( ref.current ) ) {
				this.updateSlugDebounced( ID );
				slideIsInView = true;
			}
		} );

		const firstSlide = this.slideRefs[ first( this.props.gallery ).ID ];
		const lastSlide = this.slideRefs[ last( this.props.gallery ).ID ];

		if ( ! firstSlide || ! lastSlide ) {
			return;
		}

		const firstSlideTopOffset = firstSlide.current.getBoundingClientRect().top + window.scrollY;
		const lastSlideBottomOffset = lastSlide.current.getBoundingClientRect().top + window.scrollY;

		// User is not on any slide and outside the first and last slide boundaries reset the url
		if ( ! slideIsInView && fixPathname( window.location.pathname ) !== this.props.pagePermalink && ( window.scrollY < firstSlideTopOffset || window.scrollY > lastSlideBottomOffset ) ) {
			this.removeSlug();
		}
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
			this.toggleZoomModal( event, false );
		}
	}

	/**
	 * On pop state.
	 *
	 * @param {object} event Event
	 *
	 * @return {void}
	 */
	onPopState( event ) {
		if ( this.isComponentReady && event.state.slideSlug && -1 === event.state.slideSlug.indexOf( 'http' ) ) {
			this.maybeScrollToSlide( event.state.slideSlug );
		}
	}

	/**
	 * If the configured parent element exists, append child container <div>
	 *
	 * @return {void}
	 */
	prepareListNavBarContainer() {
		if ( this.props.listNavBar.minimumNumberOfItems > parseInt( this.props.galleryCount ) ) {
			return;
		}

		// Find parent element
		const listNavBarParentElement = document.querySelector( this.props.listNavBar.parentElementQuerySelector );

		if ( listNavBarParentElement ) {
			// Apply parent element styles
			Object.assign( listNavBarParentElement.style, this.props.listNavBar.parentElementStyle );

			// Create container element
			const listNavbarContainerElement = document.createElement( 'div' );

			// Apply container element attributes and styles
			Object.assign( listNavbarContainerElement, this.props.listNavBar.containerElementAttributes );
			Object.assign( listNavbarContainerElement.style, this.props.listNavBar.containerElementAttributes.style );

			// Append container element to the parent element
			// - set opacity 0 then 1 to trigger CSS transition
			listNavbarContainerElement.style.opacity = 0;
			listNavBarParentElement.appendChild( listNavbarContainerElement );
			setTimeout( () => {
				listNavbarContainerElement.style.opacity = 1;
			}, 1 );
		}
	}

	/**
	 * Render ListNavBar
	 *
	 * @param {object} intersectSlideIndex Index of the currently intersected slide
	 *
	 * @return {void}
	 */

	renderListNavBar( intersectSlideIndex = 0 ) {
		if ( this.props.listNavBar.minimumNumberOfItems > parseInt( this.props.galleryCount ) ) {
			return;
		}

		const listNavbarContainerElement = document.getElementById( this.props.listNavBar.containerElementAttributes.id );

		if ( listNavbarContainerElement ) {
			ReactDOM.render(
				<ErrorBoundary>
					<ListNavBar
						gallery={ this.props.gallery }
						galleryCount={ this.props.galleryCount }
						listNavBarConfig={ this.props.listNavBar }
						intersectSlideIndex={ intersectSlideIndex }
					/>
				</ErrorBoundary>,
				listNavbarContainerElement
			);
		}
	}

	/**
	 * Update page slug.
	 *
	 * @param {String|int} ID Slide id.
	 *
	 * @return {void}
	 */
	updateSlug( ID ) {
		let slidePermalink;
		const slideInView = this.getSlideByID( ID );

		if ( isEmpty( slideInView ) ) {
			return;
		}

		slidePermalink = slideInView.slug;

		if ( 'true' === this.props.useIndexPermalink ) {
			slidePermalink = slideInView.position;
		}

		const newPath = this.props.pagePermalink + slidePermalink + '/';
		const loc = window.location;
		const currentPath = fixPathname( loc.pathname );

		// @todo Use this for tracking.
		this.viewedSlideIDs.push( ID );

		if ( window.history && newPath !== currentPath ) {
			window.history.replaceState( {
				slideSlug: slidePermalink,
			}, '', `${ newPath }${ loc.search }${ loc.hash }` );

			this.sendTrackingDebounced( slideInView );

			maybeTriggerAdRefresh( 'rail-bottom', this.props.railBottomAdRefreshInterval );
			maybeTriggerAdRefresh( 'adhesion', this.props.adhesionAdRefreshInterval );
		}
	}

	/**
	 * Remove slug and reset the url.
	 *
	 * @return {void}
	 */
	removeSlug() {
		if ( window.history ) {
			const loc = window.location;

			window.history.replaceState( {
				slideSlug: this.props.pagePermalink,
			}, '', `${ this.props.pagePermalink }${ loc.search }${ loc.hash }` );
		}
	}

	/**
	 * Send tracking after slug changes.
	 * Should use debounced function for two reasons
	 * 1. Getting the exact area in view on scroll is tricky so this is to prevent immediate duplicate tracking.
	 * 2. If the visitor scrolled past the slide way too quickly and didn't actually see it.
	 *
	 * @param {object} slide Slide object.
	 *
	 * @return {void}
	 */
	sendTracking( slide ) {
		// Track dimensions.
		trackDimensions( slide.ID );

		// Track page views.
		trackUrlHashChange( this.props.type );
	}

	/**
	 * Get slide index by id.
	 *
	 * @param {int} ID Slide id.
	 *
	 * @return {number} slide index.
	 */
	getSlideIndexByID( ID ) {
		let slideIndex = 0;

		if ( ! isEmpty( this.props.gallery ) ) {
			this.props.gallery.forEach( ( slide, index ) => {
				if ( parseInt( ID, 10 ) === parseInt( slide.ID, 10 ) ) {
					slideIndex = index;
				}
			} );
		}

		return slideIndex;
	}

	/**
	 * Get slide by id.
	 *
	 * @param {int} ID Slide id.
	 *
	 * @return {object} slide or empty object.
	 */
	getSlideByID( ID ) {
		const slideIndex = this.getSlideIndexByID( ID );

		return this.props.gallery[ slideIndex ] || {};
	}

	/**
	 * Scrolls window to a slide.
	 *
	 * @param {string} slug Slide's slug.
	 *
	 * @return {void}
	 */
	maybeScrollToSlide( slug ) {
		if ( ! slug ) {
			return;
		}

		let targetSlide = false;
		const _slug = slug.replace( '/', '' );

		if ( ! isEmpty( this.props.gallery ) ) {
			this.props.gallery.forEach( ( slide ) => {
				let slideSlug;

				slideSlug = slide.slug;

				if ( 'true' === this.props.useIndexPermalink ) {
					slideSlug = slide.position;
				}

				if ( slideSlug === _slug ) {
					targetSlide = slide;
				}
			} );
		}

		if ( ! targetSlide || ! this.slideRefs[ targetSlide.ID ] ) {
			return;
		}

		const slideNode = this.slideRefs[ targetSlide.ID ].current;

		if ( slideNode && slideNode.scrollIntoView ) {
			slideNode.scrollIntoView();
		}
	}

	/**
	 * Check if node is in view.
	 *
	 * @param {Object} node DOM Node.
	 *
	 * @return {boolean} True or false.
	 */
	static isNodeInView( node ) {
		if ( ! node ) {
			return false;
		}

		const nodeIsVisible = node.offsetHeight,
			nodePosition = node.getBoundingClientRect(),
			nodeMiddlePosition = ( nodePosition.height / 2 ) + nodePosition.top;

		return ( nodeIsVisible > 0 ) && ( nodeMiddlePosition >= 0 ) && ( nodeMiddlePosition <= window.innerHeight );
	}

	/**
	 * Toggle zoom modal.
	 *
	 * @param {Object}  event  Click event.
	 * @param {Boolean|null} status Status.
	 * @param {int|null}     index  Slide index.
	 *
	 * @return {void}
	 */
	toggleZoomModal( event, status = null, index = null ) {
		if ( event ) {
			event.preventDefault();
		}

		const showZoomImageModal = null === status ? ! this.state.showZoomImageModal : status;

		this.setState( {
			showZoomImageModal,
		} );

		if ( isNumber( index ) ) {
			this.setState( {
				clickedImageIndex: index,
			} );
		}

		if ( showZoomImageModal ) {
			trackGA( {
				hitType: 'event',
				eventCategory: 'vertical-gallery',
				eventAction: 'click',
				eventLabel: 'zoom-image',
			} );
		}
	}

	render() {
		const { template, ordering, gallery, i10n, socialIcons, socialIconsUseMenu, pagePermalink, twitterUserName, adsProvider } = this.props;

		const clickedSlide = gallery[ this.state.clickedImageIndex ] ? gallery[ this.state.clickedImageIndex ] : {};

		if ( isEmpty( this.props.gallery ) ) {
			return null;
		}

		return (
			<div className="c-gallery-vertical">
				<div className="c-gallery-vertical__slides" style={ ! isEmpty( gallery ) && { counterReset: `slide ${ ordering === 'asc' ? 0 : gallery.length + 1 }` } }>

					{ ! isEmpty( gallery ) && map( gallery, ( slide, index ) => {
						let slideSlug;

						slideSlug = slide.slug;

						if ( 'true' === this.props.useIndexPermalink ) {
							slideSlug = slide.position;
						}

						const location = pagePermalink + slideSlug;

						return (
							<div
								key={ slide.ID }
								className="c-gallery-vertical__slide-wrapper"
								ref={ this.slideRefs[ slide.ID ] }
								data-slide-id={ slide.ID }
								data-slide-index={ slide.position }
								data-slide-position-display={ slide.positionDisplay }
							>
								<Slide
									{ ...slide }
									template={ template }
									ordering={ ordering }
									socialIcons={ socialIcons }
									socialIconsUseMenu={ socialIconsUseMenu }
									twitterUserName={ twitterUserName }
									i10n={ i10n }
									location={ location }
									slideIndex={ index }
									toggleZoomModal={ this.toggleZoomModal }
									videoSettings={ this.props.videoSettings }
									listItemStyles={ this.props.listItemStyles }
								/>
								{ index !== findLastIndex( gallery ) && (
									<Advert
										key={ slide.ID + '-advert' }
										wrapperClass="c-gallery-vertical__advert"
										advert={ slide.ads }
										adsProvider={ adsProvider }
									/>
								) }
							</div>
						);
					} ) }

				</div>

				{ ! isEmpty( clickedSlide ) && (
					<CSSTransitionGroup transitionName="c-gallery-zoom-modal__animation-zoom" transitionEnterTimeout={ 200 } transitionLeaveTimeout={ 400 } >
						{ this.state.showZoomImageModal && (
							<ZoomModal
								image={ clickedSlide.sizes[ 'pmc-gallery-xxl' ] }
								toggleZoomModal={ this.toggleZoomModal }
								alt={ clickedSlide.alt }
							/>
						) }
					</CSSTransitionGroup>
				) }

			</div>
		);
	}
}

GalleryVertical.defaultProps = {
	template: '',
	ordering: '',
	gallery: [],
	galleryTitle: '',
	i10n: {},
	ads: {},
	adsProvider: 'boomerang',
	railBottomAdRefreshInterval: 2,
	adhesionAdRefreshInterval: 2,
	socialIcons: {},
	socialIconsUseMenu: true,
	twitterUserName: '',
	styles: {},
	pagePermalink: '',
	useIndexPermalink: '',
	type: 'vertical',
	listNavBar: {
		minimumNumberOfItems: 1000,
		parentElementQuerySelector: '',
		containerElementAttributes: {
			id: {},
		},
	},
};

GalleryVertical.propTypes = {
	template: PropTypes.string,
	ordering: PropTypes.string,
	gallery: PropTypes.array.isRequired,
	galleryTitle: PropTypes.string,
	i10n: PropTypes.object,
	ads: PropTypes.object,
	adsProvider: PropTypes.string,
	railBottomAdRefreshInterval: PropTypes.number,
	adhesionAdRefreshInterval: PropTypes.number,
	socialIcons: PropTypes.object,
	socialIconsUseMenu: PropTypes.bool,
	twitterUserName: PropTypes.string,
	styles: PropTypes.object,
	pagePermalink: PropTypes.string,
	useIndexPermalink: PropTypes.string,
	type: PropTypes.string,
};

export default GalleryVertical;
