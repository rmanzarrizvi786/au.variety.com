const clonedeep = require( 'lodash.clonedeep' );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' );
const c_span = clonedeep( c_span_prototype );

const o_more_link_prototype = require( '../o-more-link/o-more-link.blue' );
const o_more_link = clonedeep( o_more_link_prototype );
const o_more_link_desktop = clonedeep( o_more_link_prototype );

c_span.c_span_text = 'The entertainment industry’s most trusted source.';
c_span.c_span_classes = 'lrv-u-color-black u-font-family-body u-font-size-13 u-margin-b-025@mobile-max u-font-size-16@tablet u-font-size-19@desktop-xl';

o_more_link.c_link.c_link_text = 'Subscribe';
o_more_link.o_more_link_classes += ' a-hidden@tablet';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'u-color-pale-sky-2', 'lrv-u-color-black' );

o_more_link_desktop.c_link.c_link_text = 'Subscribe Today';
o_more_link_desktop.o_more_link_classes += ' a-hidden@mobile-max';
o_more_link_desktop.c_link.c_link_classes = o_more_link_desktop.c_link.c_link_classes.replace( 'u-color-pale-sky-2', 'lrv-u-color-black' );

module.exports = {
	subscribe_cta_classes: 'lrv-u-border-b-1 u-border-color-brand-secondary-40 lrv-u-padding-b-1 u-padding-b-075@tablet u-margin-b-050 u-margin-t-n050@tablet u-margin-b-125@tablet',
	subscribe_cta_inner_classes: 'lrv-u-margin-lr-auto u-max-width-75p@desktop-xl lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-u-align-items-center u-justify-content-space-between@tablet u-padding-lr-125@tablet',
	c_span,
	o_more_link,
	o_more_link_desktop,
};
