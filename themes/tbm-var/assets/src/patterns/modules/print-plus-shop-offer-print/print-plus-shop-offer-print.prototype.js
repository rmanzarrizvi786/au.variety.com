const clonedeep = require( 'lodash.clonedeep' );

// Common Span
const c_span          = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_span.c_span_url     = '';
c_span.c_span_classes = 'lrv-u-display-block lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-14 u-color-pale-sky ';

const c_span_name       = clonedeep( c_span );
c_span_name.c_span_text = 'Print Plus';
c_span_name.c_span_classes = c_span_name.c_span_classes.replace('lrv-u-font-size-14', 'lrv-u-font-size-24');
c_span_name.c_span_classes = c_span_name.c_span_classes.replace('u-color-pale-sky', 'u-color-black');
c_span_name.c_span_classes += ' lrv-u-text-transform-uppercase ';

const c_span_additional        = clonedeep( c_span );
c_span_additional.c_title_text = 'Print Plus';

const c_span_first_item       = clonedeep( c_span );
c_span_first_item.c_span_text = 'Print Plus';

const c_span_offer_cost       = clonedeep( c_span );
c_span_offer_cost.c_span_text = '$139/Year';
c_span_offer_cost.c_span_classes = c_span_offer_cost.c_span_classes.replace('lrv-u-font-size-14', 'lrv-u-font-size-28');
c_span_offer_cost.c_span_classes = c_span_offer_cost.c_span_classes.replace('u-color-pale-sky', 'u-color-black');
c_span_offer_cost.c_span_classes += 'lrv-u-margin-tb-1';

const c_span_additional_item       = clonedeep( c_span );
c_span_additional_item.c_span_text = '+ FREE Variety Tote Bag';
c_span_additional_item.c_span_classes += 'lrv-u-margin-tb-1';

const o_checks_list_first_item_details                       = clonedeep( require( '../../objects/o-checks-list/o-checks-list.prototype' ) );
o_checks_list_first_item_details.o_checks_list_classes       = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-font-size-12 lrv-u-padding-a-00 lrv-u-text-align-left o-checks-list u-border-none u-font-family-basic u-font-size-15@tablet u-list-style-type-square ';
o_checks_list_first_item_details.o_checks_list_items_classes = 'lrv-a-icon-before a-icon-square-grey';

const c_button_offer              = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-button/c-button.prototype' ) );
c_button_offer.c_button_text      = "Subscribe";
c_button_offer.c_button_url       = "https://www.pubservice.com/variety/default.aspx?PC=VY&PK=M0BI9UP";
c_button_offer.c_button_type_attr = "submit";
c_button_offer.c_button_classes   = "lrv-u-display-inline-block c-button  lrv-u-padding-lr-1 lrv-u-padding-tb-075 lrv-u-color-black lrv-u-background-color-brand-primary u-font-family-basic lrv-u-color-white:hover";

const c_lazy_image_banner                    = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' ) );
c_lazy_image_banner.c_lazy_image_classes    += 'u-width-270 lrv-u-display-inline-block a-pull-1 a-pull-up-item@mobile-max';
c_lazy_image_banner.c_lazy_image_crop_class  = 'lrv-a-crop-5x3';
c_lazy_image_banner.c_lazy_image_srcset_attr = false;
c_lazy_image_banner.c_lazy_image_sizes_attr  = false;
c_lazy_image_banner.c_lazy_image_img_classes = c_lazy_image_banner.c_lazy_image_img_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );

const c_lazy_image_offer                    = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' ) );
c_lazy_image_offer.c_lazy_image_crop_class  = 'lrv-a-crop-5x3 lrv-u-display-inline-block';
c_lazy_image_offer.c_lazy_image_srcset_attr = false;
c_lazy_image_offer.c_lazy_image_sizes_attr  = false;
c_lazy_image_offer.c_lazy_image_img_classes = c_lazy_image_offer.c_lazy_image_img_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );


module.exports = {
	print_plus_shop_offer_classes: 'lrv-u-width-100p u-height-650@tablet lrv-u-background-color-white lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-space-between lrv-u-align-items-center ',
	print_plus_shop_offer_title_classes: ' lrv-u-flex lrv-u-align-items-center lrv-u-flex-direction-column lrv-u-justify-content-center lrv-u-width-100p ',
	print_plus_shop_offer_body_classes: ' u-padding-tb-125 lrv-u-flex  lrv-u-align-items-center lrv-u-flex-direction-column ',
	c_span_name: c_span_name,
	c_span_offer_cost: c_span_offer_cost,
	c_button_offer: c_button_offer,
	c_lazy_image_banner: c_lazy_image_banner,
	c_lazy_image_offer: c_lazy_image_offer,
	c_span_first_item: c_span_first_item,
	c_span_additional_item: c_span_additional_item,
	o_checks_list_first_item_details: o_checks_list_first_item_details,
	c_span_additional_offer_items: [
		c_span_additional,
		c_span_additional
	],
	c_span: c_span,
};
