/* global ga, pmc_ga_dimensions, pmc_ga_mapped_dimensions, global_pmc_gallery_urlhashchanged, googletag, pmc_adm_gpt, pmc_comscore, pmc */
/**
 * Contains Utility functions.
 */

import { contains, each, extend, has, isArray, isObject, isFunction, isEmpty, isNumber } from 'underscore';

const adRefreshClickCount = {};
window.pmc = window.pmc || {};
window.pmc.comscoreTracked = window.pmc.comscoreTracked || [];

/**
 *
 * @param {string} type  The ad type to refresh
 * @param {int} interval The click interval before ad refresh
 */
const maybeTriggerAdRefresh = ( type, interval ) => {
	if ( ! type || ! interval ) {
		return;
	}
	if ( ! adRefreshClickCount.hasOwnProperty( type ) ) {
		adRefreshClickCount[ type ] = 0;
	}
	adRefreshClickCount[ type ] += 1;
	if ( adRefreshClickCount[ type ] >= interval ) {
		adRefreshClickCount[ type ] = 0;
		try {
			pmc.hooks.do_action( 'pmc_rotate_ads', type );
		} catch ( e ) {
			// do nothing
		}
	}
};

/**
 * Track google analytics.
 *
 * @param {object} config Configuration.
 *
 * @return {void}
 */
const trackGA = ( config ) => {
	if ( 'function' === typeof ga ) {
		ga( 'send', config );
	}
};

/**
 * Load lazy ads.
 *
 * @ticket ROP-1793
 *
 * @return {void}
 */
const lazyLoadAds = () => {
	if ( 'undefined' !== typeof googletag && googletag.cmd ) {
		googletag.cmd.push( () => {
			if ( 'undefined' !== typeof pmc_adm_gpt ) { // eslint-disable-line
				pmc_adm_gpt.load_lazy_ads(true); // eslint-disable-line
			}
		} );
	}
};

/**
 * Track GA dimensions.
 * Ticket: PMCP-1033
 *
 * @param {int} slideID Current slide object.
 *
 * @return {void}
 */
const trackDimensions = ( slideID ) => {
	if ( 'undefined' === typeof pmc_ga_dimensions || 'undefined' === typeof pmc_ga_mapped_dimensions || 'undefined' === slideID ) { // eslint-disable-line
		return;
	}

	const dimensionMapping = pmc_ga_mapped_dimensions; // eslint-disable-line
	const _dimensions = extend( {}, pmc_ga_dimensions || {} ); // eslint-disable-line

	if ( ! has( dimensionMapping, 'child-post-id' ) ) {
		return;
	}

	_dimensions[ `dimension${ dimensionMapping[ 'child-post-id' ] }` ] = slideID;

	ga( 'set', _dimensions );
};

/**
 * Track page views.
 *
 * @param {string} galleryType Gallery Type.
 *
 * @return {void}
 */
const trackUrlHashChange = function( galleryType ) {
	if ( 'function' === typeof global_pmc_gallery_urlhashchanged ) { // eslint-disable-line
		global_pmc_gallery_urlhashchanged( galleryType ); // eslint-disable-line
	}

	/**
	 * Undefined check to avoid jest throwing warnings in console.
	 */
	if ( 'undefined' === typeof global_urlhashchanged ) { // eslint-disable-line
		return;
	}

	/**
	 * `try` because pmc_comscore errors as undefined when adblocker is enabled in browser.
	 */
	try {
		global_urlhashchanged(); // eslint-disable-line

		if ( 'undefined' !== typeof pmc_comscore && 'function' === typeof pmc_comscore.pageview ) { // eslint-disable-line
			// We haven't tracked this slide yet with Comscore
			if ( -1 === window.pmc.comscoreTracked.indexOf( document.location.href ) ) {
				window.pmc.comscoreTracked.push( document.location.href );
				pmc_comscore.pageview();
			}
		}
	} catch ( error ) {
		console.warn( error ); // eslint-disable-line
	}
};

/**
 * Get hash value from url which matches pattern #!2 or #!2/
 *
 * @return {string} Hash value.
 */
const getNumberedHash = () => {
	let hashValue = '';
	const hash = window.location.hash;

	if ( ! hash ) {
		return hashValue;
	}

	hashValue = hash.match( /[#][!]\d\/?/ );

	return hashValue ? hashValue[ 0 ] : '';
};

/**
 * Update old hash to new slug.
 *
 * Existing gallery slugs use hash ( as #!3 ) numbers to decide which slide to load.
 * They also add the slug/some-text after the hash as ( #!2/img_2322-copy ) which
 * doesn't have any role in deciding what slide would be loaded.
 * This method would do three things:
 *
 * 1. Parse the slide number.
 * 2. Remove the hash.
 * 3. Update the latest slug from gallery data slides using the parsed slide number.
 *
 * @param {object} gallery Gallery slides.
 *
 * @return {void}
 */
const updateOldHashToNewSlug = ( gallery ) => {
	const numberedHash = getNumberedHash();
	const currentUrl = window.location.href;
	const hash = window.location.hash;

	if ( '' === numberedHash ) {
		return;
	}

	const matches = numberedHash.match( /\d+/ );
	const slideNumber = null !== matches ? Number( matches[ 0 ] ) : null;

	if ( null === slideNumber ) {
		return;
	}

	const slideIndex = slideNumber > 0 ? slideNumber - 1 : 0;
	const slug = gallery[ slideIndex ] ? gallery[ slideIndex ].slug : '';

	const newURL = currentUrl.replace( hash, slug + '/' );

	window.history.replaceState( {}, '', newURL );
};

/**
 * Sanitize prop types to ensure that react components get the same data type as expected.
 *
 * @param {object} data Data.
 * @param {object} propTypes Prop types.
 *
 * @return {object} data with expected prop types.
 */
const sanitizePropTypes = ( data, propTypes ) => {
	if ( isEmpty( data ) || ! isObject( data ) ) {
		return {};
	}

	each( data, ( value, key ) => {
		if ( propTypes.arrays && contains( propTypes.arrays, key ) && ! isArray( value ) ) {
			data[ key ] = [];
		} else if ( contains( propTypes.strings, key ) && 'string' !== typeof value ) {
			data[ key ] = '';
		} else if ( propTypes.booleans && contains( propTypes.booleans, key ) && 'boolean' !== typeof value ) {
			data[ key ] = !! value;
		} else if ( propTypes.objects && contains( propTypes.objects, key ) && ! ( isObject( value ) && ! isArray( value ) && ! isFunction( value ) ) ) {
			data[ key ] = {};
		} else if ( propTypes.numbers && contains( propTypes.numbers, key ) && ! isNumber( value ) ) {
			data[ key ] = parseInt( value, 10 );
		}
	} );

	return data;
};

/**
 * Check if DOMContentLoaded, add a listener if not, and execute a callback.
 *
 * @param {callback} callback function.
 *
 * @return {void}
 */
const domContentLoaded = ( callback ) => {
	if ( 'loading' !== document.readyState ) {
		callback();
	} else {
		document.addEventListener( 'DOMContentLoaded', () => callback() );
	}
};

/**
 * Remove url params if custom-next-gallery exists in url.
 *
 * @return {void}
 */
const removeCustomNextGalleryURLParam = () => {
	const currentUrl = window.location.href;

	if ( currentUrl.indexOf( '?custom-next-gallery' ) > -1 && 'undefined' !== typeof window.history ) {
		const cleanURL = currentUrl.substring( 0, currentUrl.indexOf( '?' ) );
		window.history.replaceState( {}, null, cleanURL );
	}
};

/**
 * Fix a pathname from IE that might be missing its beginning slash.
 *
 * @param {string} pathname Pathname from window.location.
 * @return {string} Fixed pathname.
 */
const fixPathname = ( pathname ) => {
	if ( pathname.indexOf( '/' ) === 0 ) {
		return pathname;
	}

	return `/${ pathname }`;
};

export {
	maybeTriggerAdRefresh,
	trackGA,
	trackDimensions,
	getNumberedHash,
	updateOldHashToNewSlug,
	domContentLoaded,
	sanitizePropTypes,
	trackUrlHashChange,
	lazyLoadAds,
	removeCustomNextGalleryURLParam,
	fixPathname,
};
