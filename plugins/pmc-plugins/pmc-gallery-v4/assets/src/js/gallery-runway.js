/* globals pmc, window */

import './../scss/gallery-runway.scss';
// Polyfills.
import 'core-js/features/set';
import 'core-js/features/map';
import 'core-js/features/promise';

import './vendor/swinxyzoom';

import React from 'react'; // eslint-disable-line
import ReactDOM from 'react-dom';
import GalleryRunway from './components/gallery-runway';
import ErrorBoundary from './components/error-boundary';
import { domContentLoaded, sanitizePropTypes } from './utils';

window.pmc = window.pmc || {};

/**
 * Create runway gallery.
 *
 * @return {Object} create gallery runway object.
 */
pmc.createGalleryRunway = () => {
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
			objects: [ 'logo', 'i10n', 'timestamp', 'styles', 'introCard', 'ads', 'socialIcons', 'runwayMenu', 'sponsoredStyle' ],
			arrays: [ 'gallery' ],
			strings: [ 'pagePermalink', 'siteTitle', 'siteUrl', 'galleryFetchUrl', 'closeButtonLink', 'sponsored', 'twitterUserName', 'adsProvider' ],
			booleans: [ 'showThumbnails', 'zoom', 'pinit', 'enableInterstitial', 'forceSameEnding', 'socialIconsUseMenu' ],
			numbers: [ 'adAfter', 'interstitialAdAfter', 'galleryId' ],
		},
	};

	/**
	 * Exported gallery data.
	 *
	 * @type {object}
	 */
	gallery.data = sanitizePropTypes( window.pmcGalleryExports || {}, gallery.propTypes );

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
					<GalleryRunway
						{ ...gallery.data }
					/>
				</ErrorBoundary>,
				element );
		}
	};

	return gallery;
};

/**
 * Do no wait for other scripts or styles to load as standard gallery is standalone,
 * mount as soon as initial HTML document has been completely loaded and parsed.
 */
domContentLoaded( () => pmc.createGalleryRunway().mountTo( 'pmc-gallery-runway' ) );
