import { configure } from '@storybook/react';

window._ = require( 'underscore' );

import '!style-loader!css-loader!./../../../build/css/gallery.css';

/**
 * Require story.
 *
 * @param {String} story Story path from js folder.
 *
 * @return void.
 */
const requireStory = ( story ) => {
	require( `./../${ story }/__stories` );
};

/**
 * Load Stories.
 */
function loadStories() {
	requireStory( 'components/gallery/header' );
	requireStory( 'components/gallery/sidebar' );
	requireStory( 'components/gallery/slider' );
	requireStory( 'components/gallery/thumbnails' );
}

configure( loadStories, module );
