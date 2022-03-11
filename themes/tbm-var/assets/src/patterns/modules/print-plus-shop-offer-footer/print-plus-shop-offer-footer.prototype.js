const clonedeep = require( 'lodash.clonedeep' );


const c_span       = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_span.c_span_text = 'Explore media business trends and issues, with:';
c_span.url         = '#';


let print_plus_shop_offer_footer_items = [
	c_span,
	c_span,
	c_span,
	c_span,
];

const c_span_footer_information       = clonedeep( c_span );
c_span_footer_information.c_span_text     = 'Above offers available for new subscribers only.';
c_span_footer_information.c_span_url      = '';
c_span_footer_information.c_span_url      = '';
c_span_footer_information.c_span_classes += ' lrv-u-padding-b-1 ';

module.exports = {
	print_plus_shop_offer_footer_classes: 'aligncenter',
	print_plus_shop_offer_footer_items: print_plus_shop_offer_footer_items,
	print_plus_shop_offer_footer_list_classes: 'lrv-a-unstyle-list lrv-u-padding-tb-1 ',
	print_plus_shop_offer_footer_list_item_classes: 'lrv-u-display-inline-flex lrv-u-margin-b-1@mobile-max',
	c_span_footer_information: c_span_footer_information,
};
