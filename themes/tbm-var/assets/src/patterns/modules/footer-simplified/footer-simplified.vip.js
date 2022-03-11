const clonedeep = require( 'lodash.clonedeep' );

const footer_simplified = clonedeep( require( './footer-simplified.prototype' ) );
const o_nav = clonedeep( require( '../../objects/o-nav/o-nav.prototype.js' ) );
const c_tagline_copyright = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype.js' ) );

c_tagline_copyright.c_tagline_classes = 'lrv-u-color-white lrv-u-font-size-14 lrv-u-font-family-secondary lrv-u-text-align-center u-margin-tb-1@mobile-max u-margin-t-075';
c_tagline_copyright.c_tagline_text = '';
c_tagline_copyright.c_tagline_markup = '© Copyright 2019 Penske Business Media, LLC.';
o_nav.o_nav_list_classes = 'lrv-a-unstyle-list u-align-items-center@mobile-max lrv-u-flex-direction-column@mobile-max lrv-u-display-inline-flex a-separator-b-1@mobile-max a-separator-spacing--b-050@mobile-max a-separator-r-1@tablet a-separator-spacing--r-1@tablet lrv-u-flex lrv-u-flex-wrap-wrap lrv-u-justify-content-center ';
o_nav.o_nav_list_item_classes = 'lrv-u-font-family-secondary u-padding-t-1 lrv-u-margin-b-050 u-color-brand-primary:hover lrv-u-font-size-14 lrv-u-color-white u-color-brand-primary:hover lrv-u-display-inline-block ';

footer_simplified.footer_classes = 'lrv-u-padding-tb-1 lrv-u-padding-lr-1 u-background-color-accent-b';
footer_simplified.o_nav = o_nav;
footer_simplified.c_tagline_copyright = c_tagline_copyright;

module.exports = footer_simplified;
