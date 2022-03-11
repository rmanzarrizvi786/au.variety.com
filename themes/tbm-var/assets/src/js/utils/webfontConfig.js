/* eslint-disable */
const varietyWebFontConfig = {

	init: function init(){

		let fontCookie = pmc.cookie.get('vy_fonts_loaded');

		if ( typeof fontCookie != 'undefined' && fontCookie ) {

				this.load( 'directload' );

		} else {

			window.addEventListener( 'load', () => {

				this.load( 'load' );

			});
		}

	},

	load: function load( type ) {

		const currentThemeUrl = this.getThemeUrl();

		let font_url = currentThemeUrl + '/assets/public/webfonts.css';

		let loadcount = this.loadcount++;

		WebFont.load(
			{
				custom: {
					families: [
						'IBM Plex Mono',
						'IBM Plex Sans:n4,n7',
						'IBM Plex Serif',
						'Graphik XX Cond',
						'Para Supreme Regular',
					],
				},

				active: function webfontsLoaderActive() {
					try {
						if( 'load' == type ) {
							pmc.cookie.set('vy_fonts_loaded', 1, 7 * 24 * 60 * 60);
						}
						console.log( 'fonts loaded ' + type );
					} catch( e ) {}
				}
			}
		);
	},

	getThemeUrl: function getThemeUrl() {

		if ( 'undefined' === typeof pmc_common_urls ) {
			return '';
		} else {
			return pmc_common_urls.current_theme_uri;
		}
	},
};

export default varietyWebFontConfig;
