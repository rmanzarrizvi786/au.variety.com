const clonedeep = require( 'lodash.clonedeep' );

const o_nav = clonedeep( require( '../../objects/o-nav/o-nav.prototype.js' ) );

o_nav.o_nav_title_text = 'Footer Menu';
o_nav.o_nav_title_id_attr = 'pmc-footer';
o_nav.o_nav_classes = 'lrv-js-MobileHeightToggle';
o_nav.o_nav_title_classes = 'lrv-js-MobileHeightToggle-trigger lrv-u-padding-lr-1 u-padding-lr-00@tablet u-padding-lr-1@desktop u-padding-b-050@mobile-max lrv-u-margin-tb-00 lrv-u-width-100p@mobile-max lrv-a-icon-after a-icon-down-caret lrv-a-icon-after:margin-l-auto lrv-a-icon-after-remove@tablet lrv-a-icon-invert u-cursor-pointer@mobile-max lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-18 lrv-u-font-size-14@tablet u-border-b-1@mobile-max u-border-color-chateau-grey';
o_nav.o_nav_list_classes += ' lrv-js-MobileHeightToggle-target lrv-u-padding-b-050 lrv-u-font-family-body lrv-u-font-size-14 lrv-u-margin-t-050@mobile-max';
o_nav.o_nav_list_item_classes = 'lrv-u-padding-lr-1 u-padding-lr-00@tablet u-padding-lr-1@desktop lrv-u-padding-b-025 lrv-u-font-size-18@mobile-max lrv-u-font-size-12 lrv-u-color-grey-light lrv-u-color-white:hover';

const c_tagline_copyright = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype.js' ) );

c_tagline_copyright.c_tagline_classes = 'lrv-u-color-white lrv-u-font-size-10 lrv-u-font-family-secondary lrv-u-text-align-center u-margin-tb-1@mobile-max u-margin-t-075';
c_tagline_copyright.c_tagline_text = '';
c_tagline_copyright.c_tagline_markup = '© Copyright 2019 Variety';

module.exports = {
	footer_simplified_classes: 'lrv-u-color-white u-background-color-accent-b',
	footer_link_classes: 'lrv-u-color-white lrv-u-font-size-16 lrv-u-font-size-14@tablet lrv-u-font-family-secondary u-letter-spacing-005 u-color-variety-primary:hover',
	c_tagline_copyright: c_tagline_copyright,
	o_nav: o_nav,
};
