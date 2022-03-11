const clonedeep = require( 'lodash.clonedeep' );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' );
const c_span = clonedeep( c_span_prototype );

c_span.c_span_text = 'Music';
c_span.c_span_url = '#';
c_span.c_span_classes = 'lrv-u-display-block lrv-u-margin-b-025 lrv-u-text-transform-uppercase u-font-family-basic lrv-u-font-size-12 u-font-size-13@tablet u-letter-spacing-009';
c_span.c_span_link_classes = 'u-color-pale-sky-2 u-color-black:hover lrv-u-display-block lrv-u-padding-t-050 lrv-u-padding-b-025';

module.exports = {
	c_span,
};
