const clonedeep = require( 'lodash.clonedeep' );

const c_span          = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_span.c_span_url     = '';
c_span.c_span_classes = ' lrv-u-display-block lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-14 u-color-pale-sky ';

const c_span_heading       = clonedeep( c_span );
c_span_heading.c_span_text = 'Variety Magazine';
c_span_heading.c_span_classes = c_span_heading.c_span_classes.replace('u-color-pale-sky', 'u-color-black');
c_span_heading.c_span_classes = c_span_heading.c_span_classes.replace('lrv-u-font-size-14', 'lrv-u-font-size-16');
c_span_heading.c_span_classes += ' lrv-u-text-transform-uppercase ';

const c_span_subheading       = clonedeep( c_span );
c_span_subheading.c_span_text = 'Print and Digital Formats';
c_span_subheading.c_span_classes = c_span_subheading.c_span_classes.replace('lrv-u-font-weight-bold', '');
c_span_subheading.c_span_classes = c_span_subheading.c_span_classes.replace('u-color-pale-sky', 'u-color-black');



module.exports = {
	print_plus_shop_offer_print_header_classes: 'lrv-u-padding-tb-1 lrv-u-width-100p lrv-u-background-color-white lrv-u-flex lrv-u-flex-direction-column lrv-u-align-items-center ',
	c_span_heading: c_span_heading,
	c_span_subheading: c_span_subheading,
};