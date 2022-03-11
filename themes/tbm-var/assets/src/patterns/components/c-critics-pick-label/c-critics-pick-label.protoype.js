const clonedeep = require( 'lodash.clonedeep' );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' );
const c_span = clonedeep( c_span_prototype );

c_span.c_span_classes = 'lrv-u-background-white u-border-t-6 lrv-u-border-color-brand-primary u-font-family-basic u-font-size-11 u-font-size-14@tablet u-font-weight-medium u-color-pale-sky-2 u-letter-spacing-2 lrv-u-text-transform-uppercase lrv-a-glue lrv-u-background-color-white a-glue--t-n125 a-glue--t-n150@tablet lrv-u-padding-r-050 lrv-u-padding-tb-025 u-padding-tb-00@mobile-max';
c_span.c_span_text = 'Critics Pick';

module.exports = {
	c_span,
};
