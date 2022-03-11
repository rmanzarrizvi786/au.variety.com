const clonedeep = require( 'lodash.clonedeep' );
const print_plus_shop_offer_print_print_basic = clonedeep( require( './print-plus-shop-offer-print.prototype' ) );


print_plus_shop_offer_print_print_basic.o_checks_list_first_item_details = [];

print_plus_shop_offer_print_print_basic.print_plus_shop_offer_classes += ' u-box-shadow-medium u-border-t-6 u-border-color-brand-primary  ';
print_plus_shop_offer_print_print_basic.print_plus_shop_offer_classes += ' a-pull-up-item@tablet a-pull-11 ';

print_plus_shop_offer_print_print_basic.c_lazy_image_banner.c_lazy_image_classes = 'lrv-a-hidden';

print_plus_shop_offer_print_print_basic.c_lazy_image_offer.c_lazy_image_src_url  = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/print-plus-shop-print-basic-offer.png'
print_plus_shop_offer_print_print_basic.c_lazy_image_offer.c_lazy_image_classes += ' lrv-u-width-300 a-pull-up-item a-pull-1 a-pull-7@tablet ';

print_plus_shop_offer_print_print_basic.c_span_first_item.c_span_text            = 'Annual Subscription';
print_plus_shop_offer_print_print_basic.c_span_name.c_span_text                  = 'Print Basic';
print_plus_shop_offer_print_print_basic.c_span_additional_item.c_span_text       = '+ FREE Variety Tote Bag';
print_plus_shop_offer_print_print_basic.c_span_offer_cost.c_span_text            = '$139/year';

const o_checks_list_offer_details_item_annual_subscription            = clonedeep( print_plus_shop_offer_print_print_basic.c_span_additional_item );
o_checks_list_offer_details_item_annual_subscription.c_span_text      = '(print issues only)';
o_checks_list_offer_details_item_annual_subscription.c_span_classes   = 'lrv-u-font-size-14 lrv-u-font-family-secondary u-color-pale-sky';
print_plus_shop_offer_print_print_basic.c_span_additional_offer_items = [
	o_checks_list_offer_details_item_annual_subscription
];

print_plus_shop_offer_print_print_basic.c_button_offer.c_button_url       = "https://www.pubservice.com/variety/default.aspx?PC=VY&PK=M9MI901";

print_plus_shop_offer_print_print_basic.print_plus_shop_offer_title_classes += 'u-background-image-slash@tablet u-height-120';


module.exports = print_plus_shop_offer_print_print_basic;
