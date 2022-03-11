const clonedeep = require( 'lodash.clonedeep' );

// Initial components and default classes
const c_span          = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_span.c_span_classes = 'lrv-u-font-family-secondary lrv-u-font-size-24 lrv-u-font-weight-bold lrv-u-display-block ';

const c_button              = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-button/c-button.prototype' ) );
c_button.c_button_type_attr = 'submit';
c_button.c_button_classes   = 'lrv-u-padding-lr-1 lrv-u-padding-tb-075 lrv-u-color-white  lrv-u-font-size-18 u-background-color-brand-primary-vip u-font-family-accent u-color-black:hover';

const c_icon          = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' ) );
c_icon.c_icon_name    = 'vip-plus-large';
c_icon.c_icon_classes = 'lrv-u-display-block lrv-u-width-100p u-max-width-635 u-width-570@desktop u-width-640@desktop-xl u-height-25';
c_icon.c_label_text   = '';

const c_button_offer         = clonedeep( c_button );
c_button_offer.c_button_text =  "Subscribe";

// Tagline
const c_span_tagline       = clonedeep( c_span );
c_span_tagline.c_span_text = 'Explore media business trends and issues, with:';
c_span_tagline.c_span_classes = 'lrv-u-font-family-secondary lrv-u-font-size-18 lrv-u-padding-tb-1 lrv-u-display-block ';

// List of VIP Offers after Tagline
const o_checks_list                       = clonedeep( require( '../../objects/o-checks-list/o-checks-list.prototype' ) );
o_checks_list.o_checks_list_classes       = 'lrv-u-font-family-secondary lrv-u-font-size-16 lrv-u-font-weight-bold u-border-none ';
o_checks_list.o_checks_list_items_classes = 'lrv-u-padding-tb-050 lrv-a-icon-before a-icon-right-triangle-red  ';

const o_checks_list_item_1             = clonedeep( require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' ) );
o_checks_list_item_1.o_check_list_text = 'Deep-Dive Special Reports Filled Every Month With Data Visualizations, Analysis and Actionable Insights';
const o_checks_list_item_2             = clonedeep( require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' ) );
o_checks_list_item_2.o_check_list_text = 'Daily Commentary on the Latest Media and Tech News';
const o_checks_list_item_3             = clonedeep( require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' ) );
o_checks_list_item_3.o_check_list_text = 'Weekly Newsletter Distilling Key Market Takeaways';
const o_checks_list_item_4             = clonedeep( require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' ) );
o_checks_list_item_4.o_check_list_text = 'Exclusive Access to Video Featuring Industry Executives at Variety Events';
o_checks_list.o_checks_list_text_items = [
	o_checks_list_item_1,
	o_checks_list_item_2,
	o_checks_list_item_3,
	o_checks_list_item_4,
];

//Image next to Tagline
const c_lazy_image = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' ) );
c_lazy_image.c_lazy_image_classes    += '';
c_lazy_image.c_lazy_image_crop_class  = 'lrv-a-crop-5x3';
c_lazy_image.c_lazy_image_srcset_attr = false;
c_lazy_image.c_lazy_image_sizes_attr  = false;
c_lazy_image.c_lazy_image_src_url     = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/shop-page-vip-offer-image.png'
c_lazy_image.c_lazy_image_img_classes = c_lazy_image.c_lazy_image_img_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );

//Call To Action Prompt
const c_icon_prompt          = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' ) );
c_icon_prompt.c_icon_name    = 'stopwatch';
c_icon_prompt.c_icon_classes = 'u-width-25 u-height-25 a-hidden@mobile-max';

const c_span_prompt_primary           = clonedeep( c_span );
c_span_prompt_primary.c_span_text     = 'Don\'t Miss Our ';
c_span_prompt_primary.c_span_classes += ' lrv-u-padding-lr-050 lrv-u-font-size-18@mobile-max';

const c_span_prompt_secondary           = clonedeep( c_span );
c_span_prompt_secondary.c_span_text     = 'Limited Time Offer';
c_span_prompt_secondary.c_span_classes += ' u-color-brand-vip-primary lrv-u-font-size-18@mobile-max';

//First Offer
const c_span_offer_title_1       = clonedeep( c_span );
c_span_offer_title_1.c_span_text = 'Monthly';
c_span_offer_title_1.c_span_classes += ' lrv-u-text-align-center lrv-u-text-transform-uppercase ';

const c_span_offer_cost_1           = clonedeep(c_span );
c_span_offer_cost_1.c_span_text     = '$29.99';
c_span_offer_cost_1.c_span_classes += ' lrv-u-text-align-center u-color-brand-vip-primary';

const c_span_offer_discount_1       = clonedeep(c_span );
c_span_offer_discount_1.c_span_text = 'Save over 40%';
c_span_offer_discount_1.c_span_classes += ' lrv-u-text-align-center ';

const c_button_offer_1         = clonedeep( c_button );
c_button_offer_1.c_button_text = 'Subscribe Now';
c_button_offer_1.c_button_url  = '#';

//Second Offer
const c_lazy_image_banner_2                 = clonedeep( c_lazy_image );
c_lazy_image_banner_2.c_lazy_image_src_url  = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/print-plus-shop-best-deal.png';
c_lazy_image_banner_2.c_lazy_image_classes += 'a-pull-3 a-pull-up-item u-width-270 u-width-270 lrv-u-background-color-transparent';

const c_lazy_image_2                = clonedeep( c_lazy_image );
c_lazy_image_2.c_lazy_image_src_url = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/shop-page-vip-offer-image.png';
c_lazy_image_2.c_lazy_image_classes = c_lazy_image_2.c_lazy_image_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );

const c_span_offer_title_2       = clonedeep(c_span );
c_span_offer_title_2.c_span_text = 'Annual';
c_span_offer_title_2.c_span_classes += ' lrv-u-text-align-center lrv-u-text-transform-uppercase ';

const c_span_offer_cost_2           = clonedeep(c_span );
c_span_offer_cost_2.c_span_text     = '$299 + 1 month free';
c_span_offer_cost_2.c_span_classes += ' u-color-brand-vip-primary';
c_span_offer_cost_2.c_span_classes += ' lrv-u-text-align-center ';

const c_span_offer_discount_2       = clonedeep(c_span );
c_span_offer_discount_2.c_span_text = 'Save over 50$';
c_span_offer_discount_2.c_span_classes += ' lrv-u-text-align-center ';

const c_button_offer_2         = clonedeep( c_button );
c_button_offer_2.c_button_url  = "";
c_button_offer_2.c_button_text = 'Subscribe Now';

//Third Offer
const c_span_offer_title_3           = clonedeep(c_span );
c_span_offer_title_3.c_span_text     = 'Corporate Subscription';
c_span_offer_title_3.c_span_classes += ' lrv-u-text-transform-uppercase u-padding-b-175 lrv-u-text-align-center ';

const c_button_offer_3         = clonedeep( c_button );
c_button_offer_3.c_button_text = 'Learn More';
c_button_offer_3.c_button_url  = '/vip-corporate-subscriptions/';

module.exports = {
	print_plus_shop_offer_vip_classes: ' lrv-u-margin-tb-1 lrv-u-padding-tb-2 lrv-u-background-color-white ',
	print_plus_shop_offer_vip_header_grid_classes: 'lrv-a-grid lrv-a-cols2@desktop lrv-u-padding-b-2 lrv-u-padding-lr-1',
	c_icon: c_icon,
	c_span_tagline: c_span_tagline,
	o_checks_list: o_checks_list,
	c_lazy_image: c_lazy_image,
	print_plus_shop_offer_vip_offers_classes: 'lrv-u-align-items-center lrv-u-flex lrv-u-flex-direction-column u-background-image-slash lrv-u-padding-b-2',
	print_plus_shop_offer_vip_prompt_classes: 'lrv-u-padding-tb-2 lrv-u-text-align-center lrv-u-align-items-center lrv-u-flex lrv-u-justify-content-center u-border-color-brand-secondary-50 u-padding-lr-60 lrv-u-padding-tb-075',
	c_icon_prompt: c_icon_prompt,
	c_span_prompt_primary: c_span_prompt_primary,
	c_span_prompt_secondary: c_span_prompt_secondary,
	print_plus_shop_offer_vip_offer_grid_classes: ' lrv-a-grid  lrv-a-cols3@desktop u-align-items-flex-end ',
	print_plus_shop_offer_vip_offer_grid__col1_classes: 'u-height-250 lrv-u-order-100@mobile-max lrv-u-align-items-center lrv-u-background-color-white lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-space-evenly lrv-u-margin-lr-2 lrv-u-padding-tb-2 lrv-u-padding-lr-1 u-box-shadow-medium',
	c_span_offer_title_1: c_span_offer_title_1,
	c_span_offer_cost_1: c_span_offer_cost_1,
	c_span_offer_discount_1: c_span_offer_discount_1,
	c_button_offer_1: c_button_offer_1,
	print_plus_shop_offer_vip_offer_grid__col2_classes: 'u-height-250 lrv-u-align-items-center lrv-u-background-color-white lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-space-evenly lrv-u-margin-lr-2 lrv-u-padding-lr-1 lrv-u-padding-tb-2 u-box-shadow-medium',
	c_lazy_image_banner_2: c_lazy_image_banner_2,
	c_span_offer_title_2: c_span_offer_title_2,
	c_span_offer_cost_2: c_span_offer_cost_2,
	c_span_offer_discount_2: c_span_offer_discount_2,
	c_button_offer_2: c_button_offer_2,
	print_plus_shop_offer_vip_offer_grid__col3_classes: 'u-height-250 lrv-u-order-100@mobile-max lrv-u-align-items-center lrv-u-background-color-white lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-space-evenly lrv-u-margin-lr-2 lrv-u-padding-tb-2 lrv-u-padding-lr-1 u-box-shadow-medium',
	c_span_offer_title_3: c_span_offer_title_3,
	c_button_offer_3: c_button_offer_3
};