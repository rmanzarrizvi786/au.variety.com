const clonedeep = require( 'lodash.clonedeep' );
const print_plus_shop_offer_footer_print = clonedeep( require( './print-plus-shop-offer-footer.prototype' ) );

const c_span               = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_span.c_span_classes      = 'u-padding-lr-125';
c_span.c_span_link_classes = 'u-border-b-1 u-border-color-brand-secondary u-color-black u-display-inline-flex';

const c_span__1       = clonedeep( c_span );
c_span__1.c_span_text = 'Frequently Asked Questions';
c_span__1.c_span_url  = 'https://www.pubservice.com/subfaq.aspx?PC=VY&AN=&Zp=&PK=';

const c_span__2       = clonedeep( c_span )
c_span__2.c_span_text = 'Corporate Subscription';
c_span__2.c_span_url  = '/corporate-subscriptions/';

const c_span__3       = clonedeep( c_span )
c_span__3.c_span_text = 'Education Subscription';
c_span__3.c_span_url  = 'https://www.pubservice.com/variety/?PC=VY&PK=S0BI9ED';

const c_span__4       = clonedeep( c_span )
c_span__4.c_span_text = 'Terms and Conditions';
c_span__4.c_span_url  = 'https://pmc.com/terms-of-use/';

print_plus_shop_offer_footer_print.print_plus_shop_offer_footer_items = [
	c_span__1,
	c_span__2,
	c_span__3,
	c_span__4,
];

print_plus_shop_offer_footer_print.print_plus_shop_offer_footer_classes += ' lrv-u-background-color-white lrv-u-padding-t-2 ',


	module.exports = print_plus_shop_offer_footer_print;
