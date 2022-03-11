import ajaxIconSprite from '@larva-js/utils/ajaxIconSprite';
import initEmailCaptures from '@larva-js/interface/EmailCapture/index';
import initMegaMenu from '@larva-js/interface/MegaMenu';
import initMobileHeightToggles from '@larva-js/interface/MobileHeightToggle/index';

import varietyWebFontConfig from '../src/js/utils/webfontConfig';
import initCollapsible from '../src/js/interface/Collapsible';
import initCxenseWidget from '../src/js/interface/CxenseWidget';
import initExpandableSearch from '../src/js/interface/ExpandableSearch';
import initFlickity from '../src/js/interface/Flickity';
import initSelectNav from '../src/js/interface/SelectNav';
import initHeader from '../src/js/interface/Header';
import initSideSkinAd from '../src/js/interface/SideSkinAd';
import initVideoShowcase from '../src/js/interface/VideoShowcase';
import lazyIframe from '../src/js/interface/LazyIframe';
import forceViewport from '../src/js/utils/forceViewport';
import initSubscription from '../src/js/interface/Subscription';
import initAddToCalendar from '../src/js/interface/AddToCalendar';

// Ajax the sprite.
const buildPath = ( () => {
	if ( undefined !== window.pmc_common_urls ) {
		return (
			window.pmc_common_urls.current_theme_uri +
			'/assets/build/svg/defs/sprite.defs.svg'
		);
	}
	return '/assets/build/svg/defs/sprite.defs.svg';
} )();

const onSafeResize = function () {
	const width = window.innerWidth;
	initMobileHeightToggles( width );
	initHeader( width );
};

window.addEventListener( 'message', ( e ) => {
	initSideSkinAd( e );
} );

window.addEventListener( 'resize', () => {
	onSafeResize();
} );

window.addEventListener( 'load', () => {
	forceViewport();
	initCollapsible();
	initCxenseWidget();
	initEmailCaptures();
	initExpandableSearch();
	initVideoShowcase();
	onSafeResize();
	initFlickity();
	initSelectNav();
	lazyIframe();
	initSubscription();
	initAddToCalendar();
} );

window.addEventListener( 'DOMContentLoaded', () => {
	initMegaMenu();
} );

varietyWebFontConfig.init();
ajaxIconSprite( buildPath );
