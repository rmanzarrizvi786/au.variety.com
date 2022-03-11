import initTabsManager from '@larva-js/interface/TabsManager';
import initScrollTo from '../src/js/interface/ScrollTo';

window.addEventListener( 'DOMContentLoaded', () => {
	initTabsManager();
	initScrollTo();
} );

window.addEventListener( 'load', () => {
	if ( 'function' === typeof jQuery ) {
		( function ( $ ) {
			$.fn.fitVids = function () {};
		} )( jQuery );
	}

	if ( 'undefined' !== typeof jwplayer ) {
		const jwConfig = {
			playlist: 'http://content.jwplatform.com/jw6/TTy91oNt.xml',
			repeat: true,
			autostart: 'viewable',
			mute: true,
			controls: true,
			stretching: 'fill',
		};
		const jwDivID = 'jwplayer_TTy91oNt_yHdpIsEW_div';

		if ( 'function' === typeof window.pmc_jwplayer ) {
			window.pmc_jwplayer( jwDivID ).setup( jwConfig );
		} else if ( 'function' === typeof window.jwplayer ) {
			window.jwplayer( jwDivID ).setup( jwConfig );
		}
	}
} );
