const clonedeep = require( 'lodash.clonedeep' );

const print_plus_shop_offer_vip_plus = clonedeep( require( './print-plus-shop-offer-vip.prototype' ) );

print_plus_shop_offer_vip_plus.c_button_offer_1.c_button_classes = "lrv-u-color-white lrv-u-font-size-18 lrv-u-padding-lr-1 lrv-u-padding-tb-075 u-background-color-brand-primary-vip u-color-black:hover u-font-family-basic";
print_plus_shop_offer_vip_plus.c_button_offer_2.c_button_classes = "lrv-u-color-white lrv-u-font-size-18 lrv-u-padding-lr-1 lrv-u-padding-tb-075 u-background-color-brand-primary-vip u-color-black:hover u-font-family-basic";
print_plus_shop_offer_vip_plus.c_button_offer_3.c_button_classes = "lrv-u-color-white lrv-u-font-size-18 lrv-u-padding-lr-1 lrv-u-padding-tb-075 u-background-color-brand-primary-vip u-color-black:hover u-font-family-basic";

module.exports = print_plus_shop_offer_vip_plus;
