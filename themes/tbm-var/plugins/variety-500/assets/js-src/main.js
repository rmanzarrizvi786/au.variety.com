// jshint es3: false
// jshint esversion: 6

import fitTextToContainer from './fitTextToContainer';
import profileNavManager from './profileNavManager';
import spotlightManager from './spotlightManager';
import statsManager from './statsManager';
import searchFilterManager from './searchFilterManager';
import siteHeaderManager from './siteHeaderManager';
import heroVideoManager from './heroVideoManager';
import homeIntroManager from './homeIntroManager';
import imageSlider from './imageSlider';
import shareLinksManager from './shareLinksManager';
import socialGlimpseManager from './socialGlimpseManager';
import VideoCarouselManager from './videoCarouselManager';
import VideoPlayerManager from './videoPlayerManager';

const $ = (window.$ = window.jQuery);

$(function () {
	'use strict';

	// Fit text content to the container width on resize.
	fitTextToContainer.init();

	// Initialize the Profile page navigation manager.
	profileNavManager.init();

	// Initialize site header manager.
	siteHeaderManager.init();

	// Initialize Search page filter manager.
	searchFilterManager.init();

	// Initialize the Spotlight manager.
	spotlightManager.init();

	// Initialize the Stats (By The Numbers) manager.
	statsManager.init();

	// Initialize hero video manager.
	heroVideoManager.init();

	// Initialize home intro manager.
	homeIntroManager.init();

	// Initialize glimpse image slider.
	imageSlider.init();

	// Initialize social share icons toggle.
	shareLinksManager.init();

	// Initialise social glimpse manager (Profile page).
	socialGlimpseManager.init();

	// Initialize video  carousel manager.
	VideoCarouselManager.init();

	// Initialize video  carousel player-.
	VideoPlayerManager.init();
});
