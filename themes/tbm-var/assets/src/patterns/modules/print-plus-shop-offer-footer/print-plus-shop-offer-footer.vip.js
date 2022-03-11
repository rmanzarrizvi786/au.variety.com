const clonedeep = require( 'lodash.clonedeep' );
const print_plus_shop_offer_footer_vip = clonedeep( require( './print-plus-shop-offer-footer.prototype' ) );

const c_span               = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_span.c_span_classes      = 'u-padding-lr-125';
c_span.c_span_link_classes = 'u-border-b-1 u-border-color-brand-secondary u-color-black u-display-inline-flex';

const c_span__1       = clonedeep( c_span );
c_span__1.c_span_text = 'Frequently Asked Questions';
c_span__1.c_span_url  = '/vip-faq/';

const c_span__2       = clonedeep( c_span )
c_span__2.c_span_text = 'Terms and Conditions';
c_span__2.c_span_url  = 'https://pmc.com/terms-of-use/';

print_plus_shop_offer_footer_vip.print_plus_shop_offer_footer_items = [
	c_span__1,
	c_span__2,
];

module.exports = print_plus_shop_offer_footer_vip;
