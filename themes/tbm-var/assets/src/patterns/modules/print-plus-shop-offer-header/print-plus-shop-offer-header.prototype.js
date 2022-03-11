const clonedeep = require( 'lodash.clonedeep' );

const c_heading_primary           = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );
c_heading_primary.c_heading_text  = 'Choose your Variety Subsrciption';
c_heading_primary.c_heading_classes += 'lrv-u-display-block';

const c_span_secondary         = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_span_secondary.c_span_text   = 'Choose your Variety Subsrciption';
c_span_secondary.c_span_classes += 'lrv-u-display-block';

module.exports = {
	print_plus_shop_offer_header_classes: 'font-family-secondary-fancy lrv-u-width-100p ' +
		'u-background-color-brand-accent-100-b u-min-height-100 lrv-u-color-white lrv-u-text-transform-uppercase ' +
		'lrv-u-font-weight-bold lrv-u-text-align-center lrv-u-padding-t-2 lrv-u-font-size-32',
	c_heading_primary: c_heading_primary,
	c_span_secondary: c_span_secondary,
};
