const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.variety-vip.js' );
const o_tease = clonedeep( o_tease_prototype );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' );
const c_span = clonedeep( c_span_prototype );

o_tease.o_tease_classes = 'lrv-u-flex u-padding-tb-125 u-padding-t-075';

o_tease.o_tease_secondary_classes = 'lrv-u-flex-shrink-0 lrv-u-margin-r-1 u-order-n1 u-width-44p@mobile-max';
	
o_tease.c_title.c_title_text = 'The Dark Side of Film Financing';
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'u-font-size-15', 'lrv-u-font-size-14' );

c_span.c_span_text = 'Biz';
c_span.c_span_url = '#';
c_span.c_span_classes = 'lrv-u-display-block lrv-u-margin-b-025 lrv-u-text-transform-uppercase u-font-family-accent u-font-size-13';
c_span.c_span_link_classes = 'u-color-brand-secondary-50';

module.exports = {
	...o_tease,
	c_span,
};
