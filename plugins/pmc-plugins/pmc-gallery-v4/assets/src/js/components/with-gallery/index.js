/* globals pmc_adm_gpt, blogherads, fetch, Promise, sessionStorage, pmc */
/**
 * High order component for common functionality.
 */
import React from 'react';
import PropTypes from 'prop-types';

import {
	maybeTriggerAdRefresh,
	trackGA,
	trackDimensions,
	updateOldHashToNewSlug,
	trackUrlHashChange,
	removeCustomNextGalleryURLParam,
	fixPathname,
} from './../../utils';

import { bindAll, isEmpty, delay, contains, isFunction, isObject, each, difference, isArray, first } from 'underscore';

/**
 * withGallery HOC.
 *
 * @param {Object} WrappedComponent React component.
 *
 * @return {Object} React component.
 */
const withGallery = ( WrappedComponent ) => {
	class WithGallery extends React.Component {
		/**
		 * Constructor
		 *
		 * @param {Object} props Component props.
		 *
		 * @return {void}
		 */
		constructor( props ) {
			super( props );

			bindAll( this,
				'onPopStateEvent',
				'beforeSlideChange',
				'afterSlideChange',
				'handleActionRotateAds',
				'updateGalleryIndex',
				'getCurrentSlide',
				'toggleInterstitial',
				'setMediumSize',
				'toggleZoomModal',
				'registerCallbacks',
				'onEscape',
				'toggleNavigationLock',
				'storeGalleryNavigation'
			);

			this.gallery = this.props.gallery || [];

			if ( isEmpty( this.gallery ) ) {
				return;
			}

			/**
			 * Determine the count of images to-be-seen for a 'content-consumed' GA event to be sent.
			 * Currently this is set to 25% of the gallery images.
			 *
			 * @type {number}
			 */
			this.contentConsumedImageCount = Math.round( this.gallery.length / 4 );
			this.contentConsumedTrackingSent = false;

			updateOldHashToNewSlug( this.gallery );
			removeCustomNextGalleryURLParam();

			this.callbacks = {};

			/**
			 * Get initial index from url.
			 * Old urls should be updated to the new one by this point.
			 *
			 * @type {number}
			 */
			this.initialGalleryIndex = this.getInitialGalleryIndex();
			this.isBrowserNavigation = false;

			this.adDivIds = this.constructor.getAdDivIds( this.props.ads );

			this.clickCount = 0;
			this.mediumSize = 800;
			this.canShowInterstitial = this.props.enableInterstitial && ( Number( this.props.interstitialAdAfter ) > 0 ) && ( Number( this.props.interstitialAdAfter ) <= this.gallery.length );

			this.canShowInterstitial = ( document.cookie.indexOf( 'scroll0=' ) > -1 ) ? false : this.canShowInterstitial;

			this.nextGalleriesCheckDone = false;

			this.noNextGalleryFoundType = 'no-next-gallery-found';

			this.noSessionStorageSupport = ( 'undefined' === typeof window.sessionStorage );

			this.storageKeys = {
				visitedGalleryIds: 'visitedGalleryIds',
				navigationMapping: 'navigationMapping',
				noNextGalleryFound: 'noNextGalleryFound',
			};

			this.storeId( this.storageKeys.visitedGalleryIds, this.props.galleryId );
			this.closeButtonLink = this.setAndGetCloseButtonLink();
			this.noNextGalleryFound = this.setAndGetNoNextGalleryFound();

			const { nextGallery } = this.props;

			const nextGalleryId = ( ! isEmpty( nextGallery ) && nextGallery.ID ) ? nextGallery.ID : '';
			const nextGalleryLink = ( ! isEmpty( nextGallery ) && nextGallery.link ) ? nextGallery.link : '';
			const nextGalleryTitle = ( ! isEmpty( nextGallery ) && nextGallery.title ) ? nextGallery.title : '';
			const nextGalleryType = ( ! isEmpty( nextGallery ) && nextGallery.type ) ? nextGallery.type : '';

			const previousGalleryLink = this.getStoredPreviousGalleryLink();

			this.state = {
				galleryIndex: this.initialGalleryIndex,
				location: window.location.href,
				showInterstitial: false,
				canLoadInterstitialAd: false,
				canLoadAds: false,
				slideIndexesToLoad: [ this.initialGalleryIndex ], // This state decides what images would be loaded.
				showZoomImageModal: false,
				isMediumSize: false,
				interstitialSlotCleared: false,
				navigationLocked: false,
				nextGalleryId,
				nextGalleryLink,
				nextGalleryTitle,
				nextGalleryType,
				previousGalleryLink,
			};

			this.storeGalleryNavigation();

			if ( typeof pmc !== 'undefined' && pmc.hooks ) {
				pmc.hooks.add_action( 'pmc_rotate_ads', this.handleActionRotateAds );
			}
		}

		handleActionRotateAds( type ) {
			if ( 'rightRailGallery' === type ) {
				this.rotateAd( 'rightRailGallery', this.props.adsProvider, this.props.ads );
			}
		}

		/**
		 * When the component has mounted.
		 *
		 * @return {void}
		 */
		componentDidMount() {
			const _delay = 1000;

			if ( isEmpty( this.gallery ) ) {
				return;
			}

			/**
			 * Load next slide image set after a second.
			 */
			delay( () => this.loadNextSlideImageSet(), _delay );

			// Outside of delay because the ads can be lazy loaded from backend.
			this.setState( {
				canLoadAds: true,
			} );

			this.setMediumSize();

			// Add event listeners.
			window.addEventListener( 'popstate', this.onPopStateEvent );
			document.addEventListener( 'keydown', this.onEscape );
			window.addEventListener( 'resize', this.setMediumSize );
		}

		/**
		 * Fires when component un-mounts.
		 *
		 * @return {void}
		 */
		componentWillUnmount() {
			document.removeEventListener( 'keydown', this.onEscape );
			window.removeEventListener( 'popstate', this.onPopStateEvent );
			window.removeEventListener( 'resize', this.setMediumSize );
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
		 * Register callbacks.
		 * To be used from the parent component.
		 *
		 * @param {Object} callbacks callbacks.
		 *
		 * @return {void}
		 */
		registerCallbacks( callbacks ) {
			this.callbacks = callbacks;
		}

		/**
		 * Load next slide image set.
		 *
		 * @return {Array} loaded slides.
		 */
		loadNextSlideImageSet() {
			const slidesLength = this.gallery.length;
			const lastIndex = slidesLength - 1;
			const slideIndexesToLoad = this.state.slideIndexesToLoad;
			const currentIndex = this.state.galleryIndex;

			// Bail out early if all slide images have been loaded.
			if ( slideIndexesToLoad.length >= slidesLength ) {
				return;
			}

			// Case 1: Visitor lands on first slide.
			if ( 0 === this.initialGalleryIndex ) {
				const slidesCount = 5;
				const startIndex = currentIndex + 1;
				const endIndex = slidesCount + startIndex;

				// Push next 5 slide indexes.
				for ( let index = startIndex; index < endIndex; index++ ) {
					if ( index <= lastIndex && ! contains( slideIndexesToLoad, index ) ) {
						slideIndexesToLoad.push( index );
					}
				}
			} else { // Case 2: Visitor lands in the middle of the slide.
				const nextSlidesCount = 3;
				const nextStartIndex = currentIndex + 1;
				const nextEndIndex = nextSlidesCount + nextStartIndex;

				const previousSlidesCount = 2;
				const previousEndIndex = currentIndex - 1;
				const previousStartIndex = currentIndex > previousSlidesCount ? currentIndex - previousSlidesCount : 0;

				// Push previous 2 slides.
				for ( let index = previousStartIndex; index <= previousEndIndex; index++ ) {
					if ( index <= lastIndex && ! contains( slideIndexesToLoad, index ) ) {
						slideIndexesToLoad.push( index );
					}
				}

				// Push next 3 slides.
				for ( let index = nextStartIndex; index < nextEndIndex; index++ ) {
					if ( index <= lastIndex && ! contains( slideIndexesToLoad, index ) ) {
						slideIndexesToLoad.push( index );
					}
				}
			}

			this.setState( {
				slideIndexesToLoad,
			} );

			// If visitor directly comes to the last slide.
			if ( currentIndex === lastIndex ) {
				this.mayBeUpdateNextGallery();
			}

			return slideIndexesToLoad;
		}

		/**
		 * Load single slide image by index.
		 *
		 * @param {int} index Slide index.
		 *
		 * @return {Array} Loaded slides.
		 */
		loadSlidesByIndex( index ) {
			const slideIndexesToLoad = this.state.slideIndexesToLoad;

			if ( ! contains( slideIndexesToLoad, index ) ) {
				slideIndexesToLoad.push( index );

				this.setState( {
					slideIndexesToLoad,
				} );
			}

			return slideIndexesToLoad;
		}

		/**
		 * Triggers before the slide changes.
		 *
		 * @param {int} currentIndex Current Slide index.
		 * @param {int} nextIndex    Next Slide index.
		 *
		 * @return {void}
		 */
		beforeSlideChange( currentIndex, nextIndex ) {
			this.updateGalleryIndex( nextIndex );
			this.updateShowInterstitial();
			this.loadSlidesByIndex( nextIndex );

			// When the slide changes call only once.
			this.mayBeUpdateNextGallery();

			if ( isFunction( this.callbacks.beforeSlideChange ) ) {
				this.callbacks.beforeSlideChange( currentIndex, nextIndex );
			}
		}

		/**
		 * Update next gallery if not done before.
		 *
		 * @return {void}
		 */
		mayBeUpdateNextGallery() {
			const { nextGallery } = this.props;

			if ( ! this.nextGalleriesCheckDone && isObject( nextGallery ) && this.noNextGalleryFoundType === nextGallery.type ) {
				this.fetchNextGalleries().then( galleries => this.updateNextGallery( galleries ) );
			}
		}

		/**
		 * Update next gallery.
		 *
		 * @param {object} galleries Gallery object.
		 *
		 * @return {void}
		 */
		updateNextGallery( galleries ) {
			if ( isEmpty( galleries ) ) {
				return;
			}

			const nextGallery = first( galleries );

			if ( isEmpty( nextGallery ) ) {
				return;
			}

			let nextGalleryLink = nextGallery.link;
			const nextGalleryTitle = nextGallery.title ? nextGallery.title : '';
			const nextGalleryId = nextGallery.ID ? nextGallery.ID : '';
			const nextGalleryType = nextGallery.type ? nextGallery.type : '';

			if ( this.noNextGalleryFound ) {
				nextGalleryLink = nextGalleryLink + '?custom-next-gallery=1'; // nextGalleryLink will not have any other parameter.
			}

			this.setState( {
				nextGalleryId,
				nextGalleryLink,
				nextGalleryTitle,
				nextGalleryType,
			}, this.storeGalleryNavigation );
		}

		/**
		 * Get ad div ids.
		 *
		 * @param {object} ads Ad object.
		 *
		 * @return {object} ad div ids.
		 */
		static getAdDivIds( ads ) {
			const adDivIds = {
				galleryInterstitial: [],
				rightRailGallery: [],
			};

			each( adDivIds, ( value, key ) => {
				if ( isObject( ads ) && ads[ key ] && ads[ key ].html && ads[ key ].data ) {
					ads[ key ].data.forEach( function( ad ) {
						adDivIds[ key ].push( ad.divId );
					} );
				}
			} );

			return adDivIds;
		}

		/**
		 * Update showInterstitial state to show ad.
		 *
		 * @return {void}
		 */
		updateShowInterstitial() {
			if ( ! this.canShowInterstitial ) {
				return;
			}

			this.clickCount++;

			const state = 0 === this.clickCount % Number( this.props.interstitialAdAfter );

			this.toggleInterstitial( state );
		}

		/**
		 * Toggle interstitial ad state.
		 *
		 * @param {Boolean} state Show or hide interstitial.
		 *
		 * @return {void}
		 */
		toggleInterstitial( state ) {
			if ( this.canShowInterstitial ) {
				this.setState( {
					showInterstitial: state,
				} );

				if ( true === state ) {
					this.rotateAd( 'galleryInterstitial', this.props.adsProvider, this.props.ads );

					this.setState( {
						canLoadInterstitialAd: true, // Load interstitial ad only after the visitor reaches the interstitial ad slide for the first time.
					} );
				} else if ( ! this.state.interstitialSlotCleared ) {
					this.clearInterstitialAdSlot(); // Clear slot when interstitial hides and only if slot was not cleared after the refresh.
				}
			}
		}

		/**
		 * Called after slide changes.
		 *
		 * @return {void}
		 */
		afterSlideChange() {
			maybeTriggerAdRefresh( 'rightRailGallery', this.props.adAfter );
			maybeTriggerAdRefresh( 'rail-bottom', this.props.railBottomAdRefreshInterval );
			maybeTriggerAdRefresh( 'adhesion', this.props.adhesionAdRefreshInterval );

			this.loadNextSlideImageSet();

			if ( ( this.state.galleryIndex + 1 ) === this.contentConsumedImageCount && ! this.contentConsumedTrackingSent ) {
				this.contentConsumedTrackingSent = true;

				trackGA( {
					hitType: 'event',
					eventCategory: 'standard-gallery',
					eventAction: 'content-consumed',
					eventLabel: 'content-consumed',
					nonInteraction: true,
				} );
			}
		}

		/**
		 * Rotate ad when the slide changes.
		 *
		 * @param {string} type        Ad type.
		 * @param {string} adsProvider Ads provider.
		 * @param {ads}    ads         Ad configuration.
		 *
		 * @return {void}
		 */
		rotateAd( type, adsProvider, ads ) {
			let interstitialAdRefreshed = false;

			if ( ! isObject( ads ) || ! ads[ type ] ) {
				return;
			}

			if ( 'boomerang' === adsProvider ) {
				const adsRefreshed = this.constructor.reloadAllBoomerangAds();

				if ( 'galleryInterstitial' === type ) {
					interstitialAdRefreshed = adsRefreshed;
				}

			} else if ( 'undefined' !== typeof pmc_adm_gpt && ads[ type ].html ) { // eslint-disable-line
				if ( 'rightRailGallery' === type ) {
					pmc_adm_gpt.remove_ads( 'default' );
					pmc_adm_gpt.refresh_ads( 'default' );
					pmc_adm_gpt.rotate_ads( 'default' );
				} else if ( 'galleryInterstitial' === type ) {
					pmc_adm_gpt.refresh_ads( 'interrupt-ads-gallery' );
					pmc_adm_gpt.rotate_ads( 'interrupt-ads-gallery' );
					interstitialAdRefreshed = true;
				}
			}

			if ( 'galleryInterstitial' === type ) {
				this.setState( {
					interstitialSlotCleared: ! interstitialAdRefreshed,
				} );
			}
		}

		/**
		 * Reload all boomerang ads.
		 *
		 * @return {boolean} True if ads were reloaded.
		 */
		static reloadAllBoomerangAds() {
			let adsReloaded = false;

			if ( 'object' === typeof blogherads && 'function' === typeof blogherads.reloadAds && blogherads.running ) {
				const adSlots = blogherads.getSlots();

				if ( adSlots ) {
					blogherads.reloadAds( adSlots );
					adsReloaded = true;
				}
			}

			return adsReloaded;
		}

		/**
		 * Clear interstitial ad slot.
		 *
		 * @return {void}
		 */
		clearInterstitialAdSlot() {
			if ( ! isObject( this.props.ads )
				|| ! this.props.ads.galleryInterstitial
				|| ! this.props.ads.galleryInterstitial.html ) {
				return;
			}

			let slotCleared = false;

			if ( 'boomerang' === this.props.adsProvider ) {
				if ( 'object' === typeof blogherads && 'function' === typeof blogherads.clearSlots && blogherads.running && this.adDivIds.galleryInterstitial ) {
					const adSlots = [];

					this.adDivIds.galleryInterstitial.forEach( function( divId ) {
						const adSlot = blogherads.getSlotById( divId );

						if ( adSlot ) {
							adSlots.push( adSlot );
						}
					} );

					if ( adSlots.length ) {
						blogherads.clearSlots( adSlots );
						slotCleared = true;
					}
				}
			} else if ( 'undefined' !== typeof pmc_adm_gpt ) { // eslint-disable-line camelcase
				pmc_adm_gpt.remove_ads( 'interrupt-ads-gallery' );
				slotCleared = true;
			}

			this.setState( {
				interstitialSlotCleared: slotCleared,
			} );
		}

		/**
		 * Toggle navigation lock for interstitial ad.
		 *
		 * @param {boolean} status Lock or unlock navigation.
		 *
		 * @return {void}
		 */
		toggleNavigationLock( status ) {
			this.setState( {
				navigationLocked: status,
			} );
		}

		/**
		 * Update gallery index and slug.
		 *
		 * @param {int} index Gallery Index.
		 *
		 * @return {void}
		 */
		updateGalleryIndex( index ) {
			/**
			 * Since setState is asynchronous and this.updateSlug requires the new index, both values should be updated at the same time.
			 */
			this.setState( () => {
				const newPath = this.updateSlug( index );
				const newState = {
					galleryIndex: index,
				};

				if ( newPath ) {
					newState.location = newPath;
				}

				return newState;
			} );
		}

		/**
		 * Set state for medium size screen.
		 *
		 * @return {void}
		 */
		setMediumSize() {
			this.setState( {
				isMediumSize: ( window.innerWidth <= this.mediumSize ),
			} );
		}

		/**
		 * Get initial slide index of the page.
		 *
		 * @return {number} Slide index.
		 */
		getInitialGalleryIndex() {
			const slug = fixPathname( window.location.pathname ).replace( this.props.pagePermalink, '' ).replace( '/', '' );
			let slideIndex = 0;

			if ( ! slug ) {
				return slideIndex;
			}

			this.gallery.forEach( ( slide, index ) => {
				if ( slide.slug === slug ) {
					slideIndex = index;
				}
			} );

			return slideIndex;
		}

		/**
		 * On pop state event for back and forth navigation.
		 *
		 * @return {void}
		 */
		onPopStateEvent() {
			this.isBrowserNavigation = true;

			this.setState( {
				galleryIndex: this.getInitialGalleryIndex(),
			} );
		}

		/**
		 * Get current Slide.
		 *
		 * @return {Object} Current Slide.
		 */
		getCurrentSlide() {
			return this.gallery[ this.state.galleryIndex ] || {};
		}

		/**
		 * Update url slug when the slide changes.
		 *
		 * @param {int} galleryIndex Gallery index which needs be used for updating slug.
		 *
		 * @return {string} new url.
		 */
		updateSlug( galleryIndex ) {
			const currentSlide = this.gallery[ galleryIndex ] || {};
			let newPath = '';

			if ( currentSlide.slug && window.history && ! this.isBrowserNavigation ) {
				newPath = this.props.pagePermalink + currentSlide.slug + '/';
				const loc = window.location;
				const currentPath = fixPathname( loc.pathname );

				if ( newPath !== currentPath ) {
					window.history.pushState( {
						slideSlug: currentSlide.slug,
					}, '', `${ newPath }${ loc.search }${ loc.hash }` );

					trackDimensions( currentSlide.ID );

					// Track page views.
					trackUrlHashChange( this.props.type );
				}
			}

			this.isBrowserNavigation = false;

			return newPath;
		}

		/**
		 * Toggle zoom modal.
		 *
		 * @param {Object}  event  Event object.
		 * @param {boolean|null} status Hide or show.
		 *
		 * @return {void}
		 */
		toggleZoomModal( event, status = null ) {
			if ( event ) {
				event.preventDefault();
			}

			this.setState( {
				showZoomImageModal: null === status ? ! this.state.showZoomImageModal : status,
			} );
		}

		/**
		 * Store id in session storage.
		 *
		 * @param {string} storageKey Storage key.
		 * @param {int}    id         Id to be stored.
		 *
		 * @return {void}
		 */
		storeId( storageKey, id ) {
			if ( this.noSessionStorageSupport || ! id ) {
				return;
			}

			const ids = this.getStoredIds( storageKey );

			if ( ! contains( ids, id ) ) {
				ids.push( id );
			}

			sessionStorage.setItem( storageKey, JSON.stringify( ids ) );
		}

		/**
		 * Get stored ids in session storage.
		 *
		 * @param {string} storageKey Storage key.
		 *
		 * @return {array} stored ids array.
		 */
		getStoredIds( storageKey ) {
			if ( this.noSessionStorageSupport ) {
				return [];
			}

			const storedIds = sessionStorage.getItem( storageKey );

			const ids = ( storedIds ) ? JSON.parse( storedIds ) : [];

			return ( isArray( ids ) ) ? ids : [];
		}

		/**
		 * Fetch next galleries.
		 *
		 * @return {Promise} Promise.
		 */
		fetchNextGalleries() {
			this.nextGalleriesCheckDone = true;

			const { galleryFetchUrl } = this.props;

			return new Promise( ( resolve, reject ) => {
				if ( 'undefined' === typeof window.fetch ) {
					reject();
				}

				fetch( galleryFetchUrl )
					.then( response => response.json() )
					.then( resp => {
						const noGalleryFound = ( resp.code && 'no_gallery_found' === resp.code );
						const unVisitedGalleries = this.getUnvisitedGalleries( resp );

						if ( isEmpty( unVisitedGalleries.ids ) && ! noGalleryFound ) {
							fetch( galleryFetchUrl + '&paged=2' )
								.then( response => response.json() )
								.then( nextResp => resolve( this.getUnvisitedGalleries( nextResp ).galleries )
								).catch( error => reject( error ) );
						} else {
							resolve( unVisitedGalleries.galleries );
						}
					} ).catch( error => reject( error ) );
			} );
		}

		/**
		 * Get unvisited gallery ids.
		 *
		 * @param {array} response Visited gallery ids.
		 *
		 * @return {object} unvisited galleries.
		 */
		getUnvisitedGalleries( response ) {
			const visitedGalleryIds = this.getStoredIds( this.storageKeys.visitedGalleryIds );
			const nextGalleries = ( response && response.success && isArray( response.data ) ) ? response.data : [];

			const galleryIds = nextGalleries.map( gallery => gallery.ID );
			const unVisitedGalleryIds = difference( galleryIds, visitedGalleryIds );

			const unVisitedGalleries = nextGalleries.filter( gallery => {
				if ( contains( unVisitedGalleryIds, gallery.ID ) ) {
					return gallery;
				}
			} );

			return {
				ids: unVisitedGalleryIds,
				galleries: unVisitedGalleries,
			};
		}

		/**
		 * Set and get close button link.
		 *
		 * @return {string} close button link.
		 */
		setAndGetCloseButtonLink() {
			const closeButtonLink = this.props.closeButtonLink;

			return closeButtonLink;
		}

		/**
		 * Set and get no next gallery found if no-next-gallery-found either in the current page load or was previously found.
		 *
		 * @return {boolean} true if found.
		 */
		setAndGetNoNextGalleryFound() {
			if ( this.noSessionStorageSupport ) {
				return false;
			}

			const storedValue = sessionStorage.getItem( this.storageKeys.noNextGalleryFound );

			if ( storedValue ) {
				return true;
			}

			if ( ! isEmpty( this.props.nextGallery ) && this.props.nextGallery.type && this.noNextGalleryFoundType === this.props.nextGallery.type ) {
				sessionStorage.setItem( this.storageKeys.noNextGalleryFound, 1 );
				return true;
			}

			return false;
		}

		/**
		 * Store gallery navigation
		 *
		 * @return {void}
		 */
		storeGalleryNavigation() {
			if ( this.noSessionStorageSupport ) {
				return;
			}

			const { galleryId, pagePermalink } = this.props;
			const { nextGalleryId, nextGalleryLink } = this.state;

			const mapping = this.getStoredNavigationMapping();

			const currentGalleryMapping = mapping[ galleryId ] ? mapping[ galleryId ] : {};
			const nextGalleryMapping = mapping[ nextGalleryId ] ? mapping[ nextGalleryId ] : {};

			if ( ! currentGalleryMapping.nextGalleryLink && nextGalleryLink ) {
				currentGalleryMapping.nextGalleryLink = nextGalleryLink;
			}

			if ( ! nextGalleryMapping.previousGalleryLink ) {
				nextGalleryMapping.previousGalleryLink = pagePermalink;
			}

			mapping[ galleryId ] = currentGalleryMapping;

			if ( nextGalleryId ) {
				mapping[ nextGalleryId ] = nextGalleryMapping;
			}

			sessionStorage.setItem( this.storageKeys.navigationMapping, JSON.stringify( mapping ) );
		}

		/**
		 * Get stored navigation mapping.
		 *
		 * @return {object} navigation mapping.
		 */
		getStoredNavigationMapping() {
			const storedMapping = sessionStorage.getItem( this.storageKeys.navigationMapping );
			const mapping = ( storedMapping ) ? JSON.parse( storedMapping ) : {};

			return isObject( mapping ) ? mapping : {};
		}

		/**
		 * Get gallery navigation.
		 *
		 * @return {string} Previous gallery link.
		 */
		getStoredPreviousGalleryLink() {
			const { galleryId } = this.props;
			let previousGalleryLink = '';

			const mapping = this.getStoredNavigationMapping();

			if ( mapping[ galleryId ] ) {
				previousGalleryLink = mapping[ galleryId ].previousGalleryLink ? mapping[ galleryId ].previousGalleryLink : previousGalleryLink;
			}

			return previousGalleryLink;
		}

		render() {
			if ( isEmpty( this.gallery ) ) {
				return null;
			}

			return <WrappedComponent
				{ ...this.props }
				beforeSlideChange={ this.beforeSlideChange }
				afterSlideChange={ this.afterSlideChange }
				updateGalleryIndex={ this.updateGalleryIndex }
				getCurrentSlide={ this.getCurrentSlide }
				galleryIndex={ this.state.galleryIndex }
				location={ this.state.location }
				initialGalleryIndex={ this.initialGalleryIndex }
				showInterstitial={ this.state.showInterstitial }
				canLoadInterstitialAd={ this.state.canLoadInterstitialAd }
				canLoadAds={ this.state.canLoadAds }
				slideIndexesToLoad={ this.state.slideIndexesToLoad }
				navigationLocked={ this.state.navigationLocked }
				toggleNavigationLock={ this.toggleNavigationLock }
				toggleInterstitial={ this.toggleInterstitial }
				toggleZoomModal={ this.toggleZoomModal }
				showZoomImageModal={ this.state.showZoomImageModal }
				isMediumSize={ this.state.isMediumSize }
				registerCallbacks={ this.registerCallbacks }
				previousGalleryLink={ this.state.previousGalleryLink }
				nextGalleryLink={ this.state.nextGalleryLink }
				nextGalleryTitle={ this.state.nextGalleryTitle }
				nextGalleryType={ this.state.nextGalleryType }
				closeButtonLink={ this.closeButtonLink }
			/>;
		}
	}

	WithGallery.defaultProps = {
		ads: {},
		gallery: [],
		pagePermalink: '',
		adAfter: 1,
		adsProvider: 'boomerang',
		railBottomAdRefreshInterval: 2,
		adhesionAdRefreshInterval: 2,
		enableInterstitial: true,
		interstitialAdAfter: 5,
		type: 'horizontal',
		galleryFetchUrl: '',
		galleryId: 0,
		closeButtonLink: '',
	};

	WithGallery.propTypes = {
		ads: PropTypes.object,
		gallery: PropTypes.array.isRequired,
		pagePermalink: PropTypes.string,
		adAfter: PropTypes.number,
		adsProvider: PropTypes.string,
		railBottomAdRefreshInterval: PropTypes.number,
		adhesionAdRefreshInterval: PropTypes.number,
		enableInterstitial: PropTypes.bool,
		interstitialAdAfter: PropTypes.number,
		type: PropTypes.string,
		galleryFetchUrl: PropTypes.string,
		galleryId: PropTypes.number,
		closeButtonLink: PropTypes.string,
	};

	return WithGallery;
};

export default withGallery;
