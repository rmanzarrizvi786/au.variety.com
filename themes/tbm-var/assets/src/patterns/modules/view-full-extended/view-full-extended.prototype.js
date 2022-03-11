const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link = clonedeep( c_link_prototype );

c_link.c_link_text = 'Read the Special Report From VIP';
c_link.c_link_classes = 'lrv-u-width-100p lrv-u-background-color-black lrv-u-padding-tb-1 lrv-u-padding-lr-2  lrv-u-color-white lrv-u-color-white:hover lrv-u-background-color-brand-primary:hover  lrv-u-display-block lrv-u-font-size-18 lrv-u-margin-t-050 lrv-u-padding-tb-025 lrv-u-text-align-center lrv-u-text-transform-uppercase u-font-family-accent u-letter-spacing-001 a-hidden@mobile-max';

const c_link_mobile = clonedeep( c_link );
c_link_mobile.c_link_text = 'Read the Special Report From VIP';
c_link_mobile.c_link_classes = 'lrv-u-width-300 lrv-u-background-color-black lrv-u-padding-tb-1 lrv-u-padding-lr-2 lrv-u-background-color-black lrv-u-color-white lrv-u-padding-lr-1 lrv-u-color-white:hover lrv-u-background-color-brand-primary:hover  lrv-u-display-block lrv-u-font-size-18 lrv-u-margin-tb-2 lrv-u-margin-lr-2 lrv-u-text-align-center lrv-u-text-transform-uppercase u-font-weight-bold u-font-family-accent u-letter-spacing-001 a-hidden@tablet';

module.exports = {
	view_full_extended_classes: 'lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-u-align-items-center lrv-u-justify-content-center lrv-u-margin-lr-auto u-max-width-618',
	c_link,
	c_link_mobile,
};
