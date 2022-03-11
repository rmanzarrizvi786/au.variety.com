const clonedeep = require( 'lodash.clonedeep' );

const print_plus_shop_offer_vip_plus = clonedeep( require( './print-plus-shop-offer-vip.prototype' ) );
print_plus_shop_offer_vip_plus.print_plus_shop_offer_vip_classes += ' a-pull-up-item@tablet a-pull-10 u-box-shadow-menu  u-border-t-6 u-border-color-vip-brand-primary ';

module.exports = print_plus_shop_offer_vip_plus;
