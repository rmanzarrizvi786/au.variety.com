const clonedeep = require( 'lodash.clonedeep' );

const print_plus_shop_offer_print_print_plus = clonedeep( require( './print-plus-shop-offer-print.prototype' ) );

print_plus_shop_offer_print_print_plus.print_plus_shop_offer_classes += ' u-box-shadow-medium u-border-t-6@tablet u-border-color-brand-primary ';
print_plus_shop_offer_print_print_plus.print_plus_shop_offer_classes += ' a-pull-up-item@tablet a-pull-11 ';

print_plus_shop_offer_print_print_plus.c_lazy_image_offer.c_lazy_image_src_url  = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/print-plus-shop-print-plus-offer.png';

print_plus_shop_offer_print_print_plus.c_lazy_image_banner.c_lazy_image_src_url = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/print-plus-shop-best-deal.png'

print_plus_shop_offer_print_print_plus.c_span_first_item.c_span_text = 'Annual Subscription';

print_plus_shop_offer_print_print_plus.c_button_offer.c_button_url       = "https://www.pubservice.com/variety/default.aspx?PC=VY&PK=M0BI9UP";

print_plus_shop_offer_print_print_plus.c_lazy_image_offer.c_lazy_image_classes += ' lrv-u-width-300 a-pull-up-item a-pull-1 ';

const o_checks_list_first_item_detail_1             = clonedeep( require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' ) );
o_checks_list_first_item_detail_1.o_check_list_text = 'Print Edition';
const o_checks_list_first_item_detail_2             = clonedeep( require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' ) );
o_checks_list_first_item_detail_2.o_check_list_text = 'Digital Edition';

const o_checks_list_offer_details_item_1         = clonedeep( print_plus_shop_offer_print_print_plus.c_span );
o_checks_list_offer_details_item_1.c_span_text   = "Access to Special Issues"
const o_checks_list_offer_details_item_2       = clonedeep( print_plus_shop_offer_print_print_plus.c_span );
o_checks_list_offer_details_item_2.c_span_text = "Access to 15 Years of Variety Archives"

print_plus_shop_offer_print_print_plus.c_span_name.c_span_text            = 'Print Plus';
print_plus_shop_offer_print_print_plus.c_span_additional_item.c_span_text = '+ FREE Variety Tote Bag';
print_plus_shop_offer_print_print_plus.c_span_offer_cost.c_span_text      = '$149/year';
print_plus_shop_offer_print_print_plus.o_checks_list_first_item_details.o_checks_list_text_items   = [
	o_checks_list_first_item_detail_1,
	o_checks_list_first_item_detail_2,
];

print_plus_shop_offer_print_print_plus.c_span_additional_offer_items = [
	o_checks_list_offer_details_item_1,
	o_checks_list_offer_details_item_2,
];


print_plus_shop_offer_print_print_plus.print_plus_shop_offer_title_classes += ' u-background-image-slash@tablet u-height-120';



module.exports = print_plus_shop_offer_print_print_plus;
