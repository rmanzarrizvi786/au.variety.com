/* globals window, pmc */

import './../scss/gallery.scss';

// Polyfills.
import 'core-js/features/set';
import 'core-js/features/map';
import 'core-js/features/promise';

import React from 'react'; // eslint-disable-line
import ReactDOM from 'react-dom';
import Gallery from './components/gallery';
import ErrorBoundary from './components/error-boundary';
import { domContentLoaded, sanitizePropTypes } from './utils';
import { isEmpty } from 'underscore';

window.pmc = window.pmc || {};

/**
 * Create gallery.
 *
 * @return {Object}
 */
pmc.createGallery = () => {

	/**
	 * Gallery Object.
	 *
	 * @type {Object}
	 */
	const gallery = {
		/**
		 * Prop types.
		 *
		 * @type {object}
		 */
		propTypes: {
			objects: [ 'logo', 'i10n', 'timestamp', 'styles', 'introCard', 'ads', 'socialIcons', 'sponsoredStyle' ],
			arrays: [ 'gallery' ],
			strings: [ 'pagePermalink', 'siteTitle', 'siteUrl', 'galleryFetchUrl', 'closeButtonLink', 'sponsored', 'twitterUserName', 'adsProvider', 'mobileCloseButton' ],
			booleans: [ 'showThumbnails', 'zoom', 'pinit', 'enableInterstitial', 'forceSameEnding', 'isMobile', 'socialIconsUseMenu' ],
			numbers: [ 'adAfter', 'interstitialAdAfter', 'galleryId' ],
		}
	};

	/**
	 * Exported gallery data.
	 *
	 * @type {object}
	 */
	gallery.data = sanitizePropTypes( window.pmcGalleryExports || {}, gallery.propTypes );

	// Clean up interface and show message when gallery is empty
	if ( isEmpty( gallery.data.gallery ) ) {
		gallery.data.showThumbnails = false;
		gallery.data.timestamp = {};
		gallery.data.gallery = [ {
			title: 'Oops! This gallery is empty.',
		} ];
	}

	/**
	 * Render component.
	 *
	 * @param {String} id Node id.
	 *
	 * @return {void}
	 */
	gallery.mountTo = ( id ) => {
		const element = document.getElementById( id );

		if ( element ) {
			ReactDOM.render(
				<ErrorBoundary>
					<Gallery
						{ ...gallery.data }
					/>
				</ErrorBoundary>,
				element
			);
		}
	};

	return gallery;

};

/**
 * Do no wait for other scripts or styles to load as standard gallery is standalone,
 * mount as soon as initial HTML document has been completely loaded and parsed.
 */
domContentLoaded( () => pmc.createGallery().mountTo( 'pmc-gallery' ) );
