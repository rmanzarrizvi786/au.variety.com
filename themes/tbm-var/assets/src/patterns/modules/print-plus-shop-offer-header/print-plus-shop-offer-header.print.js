const clonedeep = require( 'lodash.clonedeep' );

const c_heading_primary          = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );
const c_span_secondary        = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_heading_primary.c_heading_text    = 'Choose Your Variety Magazine Subscription';
c_heading_primary.c_heading_classes = 'lrv-u-font-family-secondary u-font-size-32 lrv-u-display-block lrv-u-text-transform-uppercase lrv-u-font-weight-bold lrv-u-text-align-center lrv-u-padding-tb-050';

c_span_secondary.c_span_text    = '';
c_span_secondary.c_span_classes = 'lrv-u-font-family-secondary u-font-size-19 lrv-u-display-block lrv-u-font-weight-normal lrv-u-text-align-center lrv-u-padding-tb-050';

module.exports = {
	print_plus_shop_offer_header_classes: 'lrv-u-width-100p lrv-u-padding-t-1 ' +
		'u-background-color-brand-accent-100-b u-min-height-200 u-height-auto@mobile-max lrv-u-color-white ',
	c_heading_primary: c_heading_primary,
};
