const clonedeep = require( 'lodash.clonedeep' );
const o_nav_prototype = require( '../../objects/o-nav/o-nav.prototype.js' );
const o_nav = clonedeep( o_nav_prototype );

o_nav.o_nav_title_text = 'Footer Menu';
o_nav.o_nav_title_id_attr = 'pmc-footer';
o_nav.o_nav_classes = 'lrv-js-MobileHeightToggle';
o_nav.o_nav_title_classes = 'lrv-js-MobileHeightToggle-trigger lrv-u-padding-lr-1 u-padding-lr-00@tablet u-padding-lr-1@desktop u-padding-b-050@mobile-max lrv-u-margin-tb-00 lrv-u-width-100p@mobile-max lrv-a-icon-after a-icon-down-caret lrv-a-icon-after:margin-l-auto lrv-a-icon-after-remove@tablet lrv-a-icon-invert u-cursor-pointer@mobile-max lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-18 lrv-u-font-size-14@tablet u-border-b-1@mobile-max u-border-color-chateau-grey';
o_nav.o_nav_list_classes += ' lrv-js-MobileHeightToggle-target lrv-u-padding-b-050 lrv-u-font-family-body lrv-u-font-size-14 lrv-u-margin-t-050@mobile-max';
o_nav.o_nav_list_item_classes = 'lrv-u-padding-lr-1 u-padding-lr-00@tablet u-padding-lr-1@desktop lrv-u-padding-b-025 lrv-u-font-size-18@mobile-max lrv-u-font-size-12 lrv-u-color-grey-light lrv-u-color-white:hover';

const o_nav_first = clonedeep( o_nav );
o_nav_first.o_nav_title_classes += ' a-hidden@tablet';

module.exports = {
	footer_menu_classes: "lrv-a-grid u-grid-gap-050@mobile-max a-cols5@tablet lrv-u-color-white u-background-color-accent-b",
	o_navs: [
		o_nav_first,
		o_nav,
		o_nav,
		o_nav,
		o_nav,
	]
};
