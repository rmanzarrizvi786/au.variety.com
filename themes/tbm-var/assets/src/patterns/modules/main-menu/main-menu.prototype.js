const clonedeep = require( 'lodash.clonedeep' );

const o_nav_icon_prototype = require( '../../objects/o-nav-icon/o-nav-icon.prototype.js' );
const o_nav_icon = clonedeep( o_nav_icon_prototype );

const o_nav_items_o_icon_button__prototype = require( '../../objects/o-icon-button/o-icon-button.prototype.js' );

o_nav_icon.o_nav_classes = 'lrv-u-flex lrv-u-align-items-center lrv-u-margin-lr-auto u-height-38 u-max-width-1175@tablet';
o_nav_icon.o_nav_list_items = [];
const menuLinks = [ 'Film', 'TV', 'Music', 'Tech', 'Theater', 'Real Estate', 'Awards', 'Artisans', 'Video', 'V500', 'icon' ];

for (let i = 0; i < menuLinks.length; i++) {
	let o_icon_button = clonedeep( o_nav_items_o_icon_button__prototype );

	o_icon_button.o_icon_button_url = '#page';
	o_icon_button.c_icon.c_icon_name = null;
	o_icon_button.c_icon.c_icon_classes = 'lrv-u-display-none';

	if ( 'icon' === menuLinks[i] ) {
		o_icon_button.is_vip_plus_animated = true;
		o_icon_button.c_span.c_span_text = '';
		o_icon_button.c_span.c_span_classes = '';
		o_icon_button.o_icon_button_classes = 'lrv-a-unstyle-link lrv-u-padding-lr-050 lrv-u-flex lrv-u-align-items-center lrv-u-height-100p';

	// Note: The PHP object looks for the last item in menuLinks to be the AiA logo. If that changes
	// in here, the PHP obj. needs to be updated as well.
	} else {
		o_icon_button.c_span.c_span_text = menuLinks[i];
		o_icon_button.o_icon_button_classes = 'lrv-a-unstyle-link u-color-pale-grey a-font-secondary-bold-xs lrv-u-padding-tb-050 lrv-u-padding-lr-050 u-letter-spacing-001 u-color-brand-accent-20:hover';
	}

	o_nav_icon.o_nav_list_items.push( o_icon_button );
}

o_nav_icon.o_nav_list_classes = 'lrv-a-unstyle-list lrv-u-flex lrv-u-justify-content-space-between lrv-u-font-family-primary lrv-u-padding-lr-1 lrv-u-width-100p';

module.exports = {
	main_menu_classes: 'a-hidden@mobile-max u-background-color-brand-accent-100-b lrv-u-border-t-1 u-border-b-1 u-border-color-brand-secondary-30 lrv-u-color-white lrv-u-text-transform-uppercase',
	o_nav_icon,
};
