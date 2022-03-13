/* globals pmc, window */

import './../scss/gallery-vertical.scss';

// Polyfills.
import 'core-js/features/set';
import 'core-js/features/map';
import 'core-js/features/promise';

import React from 'react'; // eslint-disable-line
import ReactDOM from 'react-dom';
import GalleryVertical from './components/gallery-vertical';
import ErrorBoundary from "./components/error-boundary";
import { domContentLoaded, sanitizePropTypes } from './utils';

window.pmc = window.pmc || {};

/**
 * Create gallery vertical.
 *
 * @return {Object} Gallery object.
 */
pmc.createGalleryVertical = () => {
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
			objects: [ 'i10n', 'ads', 'socialIcons', 'styles' ],
			arrays: [ 'gallery' ],
			strings: [ 'galleryTitle', 'twitterUserName', 'pagePermalink', 'type', 'adsProvider' ],
			booleans: [ 'socialIconsUseMenu' ],
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
	 * @param {string} id Node id.
	 *
	 * @return {void}
	 */
	gallery.mountTo = ( id ) => {
		const element = document.getElementById( id );

		if ( element ) {
			ReactDOM.render(
				<ErrorBoundary>
					{ typeof gallery.data.previousPageLink !== 'undefined' && gallery.data.previousPageLink.length > 0 && (
						<div className="c-gallery-vertical__load-button">
							<a
								className="a-content-ignore"
								style={ gallery.data.listItemStyles.paginationButtonStyle }
								href={ gallery.data.previousPageLink }
							>
								Load Previous
							</a>
						</div>
					) }

					<GalleryVertical
						{ ...gallery.data }
					/>
					{ typeof gallery.data.nextPageLink !== 'undefined' && gallery.data.nextPageLink.length > 0 && (
						<div className="c-gallery-vertical__load-button">
							<a
								className="a-content-ignore"
								style={ gallery.data.listItemStyles.paginationButtonStyle }
								href={ gallery.data.nextPageLink }
							>
								Load More
							</a>
						</div>
					) }
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
domContentLoaded( () => pmc.createGalleryVertical().mountTo( 'pmc-gallery-vertical' ) );
