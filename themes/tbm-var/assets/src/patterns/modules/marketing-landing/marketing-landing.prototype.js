const clonedeep = require( 'lodash.clonedeep' );

// Initial components and default classes
const c_span          = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_span.c_span_classes = 'lrv-u-font-family-secondary lrv-u-font-size-13 lrv-u-text-align-center ';
c_span.c_span_text    = 'Subscribe';

const c_link          = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' ) );

const c_button              = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-button/c-button.prototype' ) );
c_button.c_button_type_attr = 'submit';
c_button.c_button_classes   = 'lrv-u-padding-lr-1 lrv-u-padding-tb-075 lrv-u-color-white u-background-color-brand-primary-vip u-font-family-accent lrv-u-font-size-18 u-color-black:hover';

const o_checks_list                       = clonedeep( require( '../../objects/o-checks-list/o-checks-list.prototype' ) );
o_checks_list.o_checks_list_classes       = 'u-font-family-basic lrv-u-font-size-12 u-font-size-15@tablet u-border-none u-list-style-position-inside lrv-u-text-align-center';
o_checks_list.o_checks_list_items_classes = 'lrv-u-padding-tb-050';

const o_checks_list_item = clonedeep( require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' ) );

const c_lazy_image                    = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' ) );
c_lazy_image.c_lazy_image_crop_class  = '';
c_lazy_image.c_lazy_image_srcset_attr = false;
c_lazy_image.c_lazy_image_sizes_attr  = false;
c_lazy_image.c_lazy_image_classes = c_lazy_image.c_lazy_image_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );
c_lazy_image.c_lazy_image_img_classes = c_lazy_image.c_lazy_image_img_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );

const c_span__row5       = clonedeep(c_span );
c_span__row5.c_span_text = 'Meet the VIP Team';

//Row 1
const c_span__row1_message          = clonedeep( c_span );
c_span__row1_message.c_span_text    = 'Master The Media Business';
c_span__row1_message.c_span_classes = 'u-letter-spacing-2 u-line-height-1 u-font-size-50 u-font-size-70@tablet lrv-u-font-family-primary lrv-u-text-align-center lrv-u-text-transform-uppercase ';

const c_span__row1_information          = clonedeep( c_span );
c_span__row1_information.c_span_text    = 'Explore the trends and issues that matter most to industry professionals navigating the age of disruption.';
c_span__row1_information.c_span_classes = 'lrv-u-text-align-center lrv-u-padding-tb-1 lrv-u-font-size-16 u-font-size-19@tablet u-font-family-secondary@tablet ';

const c_button__row1_button         	= clonedeep( c_button );
c_button__row1_button.c_button_text 	= 'Subscribe Now';
c_button__row1_button.c_button_classes += ' lrv-u-border-a-0 lrv-u-font-size-18 ';
c_button__row1_button.c_button_url      = '#offers';

const c_span__row1_link               = clonedeep( c_span );
c_span__row1_link.c_span_text         = 'Learn More';
c_span__row1_link.c_span_url          = '#explanation';
c_span__row1_link.c_span_classes     += ' lrv-u-padding-tb-2 lrv-u-font-size-13@desktop ';
c_span__row1_link.c_span_link_classes = 'u-border-b-1 u-border-color-brand-secondary lrv-u-color-white u-display-inline-flex';

//Row 2
const c_lazy_image__row2                    = clonedeep( c_lazy_image );
c_lazy_image__row2.c_lazy_image_src_url     = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/marketing-image-row1.png'
c_lazy_image__row2.c_lazy_image_img_classes = c_lazy_image__row2.c_lazy_image_img_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );

const c_span__row2_number          = clonedeep( c_span );
c_span__row2_number.c_span_text    = '1';
c_span__row2_number.c_span_classes = 'u-background-color-brand-primary-vip lrv-u-border-radius-50p lrv-u-color-white lrv-u-font-size-32 lrv-u-display-block lrv-u-margin-lr-auto lrv-u-text-align-center u-width-40 u-height-40 u-font-family-accent ';

const c_span__row2_information          = clonedeep( c_span );
c_span__row2_information.c_span_text    = 'Deep-Dive Special Reports';
c_span__row2_information.c_span_classes = 'lrv-u-font-size-32 u-font-size-47@tablet lrv-u-text-align-center lrv-u-padding-lr-1 u-font-family-basic ';

const o_checks_list__row2                       = clonedeep( o_checks_list );
o_checks_list__row2.o_checks_list_items_classes = 'lrv-u-padding-tb-050 u-font-family-secondary lrv-u-font-size-16 ';

const c_span__row2_list__item1      = clonedeep( c_span );
c_span__row2_list__item1.c_span_text = 'A growing collection of meticulously researched special reports filled every month with data visualizations, analysis, and actionable insights';

const c_span__row2_list__item2      = clonedeep( c_span );
c_span__row2_list__item2.c_span_text =  'Also available to produce commissioned reports.';

const c_link__row2_list__item2           = clonedeep ( c_link );
c_link__row2_list__item2.c_link_url      = 'mailto:eic@variety.com';
c_link__row2_list__item2.c_link_text     = 'Inquire Within';
c_link__row2_list__item2.c_link_classes += ' u-text-decoration-underline ';
//Row 3
const c_lazy_image__row3                    = clonedeep( c_lazy_image );
c_lazy_image__row3.c_lazy_image_src_url     = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/marketing-image-row2.png'
c_lazy_image__row3.c_lazy_image_img_classes = c_lazy_image__row3.c_lazy_image_img_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );

const c_span__row3_number          = clonedeep( c_span );
c_span__row3_number.c_span_text    = '2';
c_span__row3_number.c_span_classes = 'u-background-color-brand-primary-vip lrv-u-border-radius-50p lrv-u-color-white lrv-u-font-size-32 lrv-u-display-block lrv-u-margin-lr-auto lrv-u-text-align-center u-width-40 u-height-40 u-font-family-accent ';

const c_span__row3_information          = clonedeep( c_span );
c_span__row3_information.c_span_text    = 'Commentary on the Latest Developments in Media';
c_span__row3_information.c_span_classes = 'lrv-u-font-size-32 u-font-size-47@tablet lrv-u-text-align-center lrv-u-padding-lr-1 u-font-family-basic ';

const o_checks_list__row3                       = clonedeep( o_checks_list );
o_checks_list__row3.o_checks_list_items_classes = 'lrv-u-padding-tb-050 u-font-family-secondary lrv-u-font-size-16 ';

const o_checks_list__row3_item_1             = clonedeep( o_checks_list_item );
o_checks_list__row3_item_1.o_check_list_text = 'Daily viewpoints on top news stories, and their industry implications';
const o_checks_list__row3_item_2             = clonedeep( o_checks_list_item );
o_checks_list__row3_item_2.o_check_list_text = 'Our weekly Take 5 newsletter delivered straight to your inbox';

o_checks_list__row3.o_checks_list_text_items = [
	o_checks_list__row3_item_1,
	o_checks_list__row3_item_2,
];

//Row 4
const c_lazy_image__row4                     = clonedeep( c_lazy_image );
c_lazy_image__row4.c_lazy_image_img_classes += ' lrv-u-order-100@mobile-max  ';
c_lazy_image__row4.c_lazy_image_src_url      = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/marketing-image-row3.png'
c_lazy_image__row4.c_lazy_image_classes      = c_lazy_image__row4.c_lazy_image_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );

const c_span__row4_number               = clonedeep( c_span );
c_span__row4_number.c_span_text         = '3';
c_span__row4_number.c_span_classes      = 'u-background-color-brand-primary-vip lrv-u-border-radius-50p lrv-u-color-white lrv-u-font-size-32 lrv-u-display-block lrv-u-margin-lr-auto lrv-u-text-align-center u-width-40 u-height-40 u-font-family-accent ';

const c_span__row4_information          = clonedeep( c_span );
c_span__row4_information.c_span_text    = 'Exclusive Video Featuring Execs at Variety Events';
c_span__row4_information.c_span_classes = 'lrv-u-font-size-32 u-font-size-47@tablet lrv-u-padding-lr-1 lrv-u-text-align-center u-font-family-basic ';

const c_span__row4_message              = clonedeep( c_span );
c_span__row4_message.c_span_text        = 'Full-length videos of select keynote speakers and panels from recent Variety events are available exclusively to VIP subscribers.';
c_span__row4_message.c_span_classes     = 'u-font-family-secondary lrv-u-font-size-16 lrv-u-text-align-center ';

//Row 5 - Meet The Team
const c_span__row5_message          = clonedeep( c_span );
c_span__row5_message.c_span_text    = 'Meet The VIP Team';
c_span__row5_message.c_span_classes = 'lrv-u-display-block lrv-u-font-size-32 lrv-u-text-transform-uppercase u-border-color-brand-secondary-40 u-border-t-6@mobile-max lrv-u-font-family-primary u-font-family-secondary@tablet u-letter-spacing-040@mobile-max lrv-u-margin-t-2@mobile-max u-padding-t-250@tablet lrv-u-text-align-center@mobile-max u-font-weight-bold@tablet ';

// Team Member 1
const c_lazy_image__row5_image1                     = clonedeep( c_lazy_image );
c_lazy_image__row5_image1.c_lazy_image_src_url      = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/marketing-image-wallenstein-andy.png'
c_lazy_image__row5_image1.c_lazy_image_classes      = c_lazy_image__row5_image1.c_lazy_image_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );
c_lazy_image__row5_image1.c_lazy_image_classes     += ' u-width-50p@tablet  lrv-a-hidden@mobile-max ';

const c_lazy_image__row5_image_divider          = clonedeep( c_span );
c_lazy_image__row5_image_divider.c_span_text    = '';
c_lazy_image__row5_image_divider.c_span_classes = 'lrv-a-hidden@mobile-max lrv-u-border-t-1 u-border-color-vip-brand-primary u-margin-tb-125 u-width-25p';

const c_span__row5_image1_name              = clonedeep( c_span );
c_span__row5_image1_name.c_span_text        = 'Andrew Wallenstein';
c_span__row5_image1_name.c_span_classes     = 'lrv-u-display-block lrv-u-font-family-secondary lrv-u-padding-t-1 lrv-u-border-t-1@mobile-max lrv-u-text-align-center u-border-color-pale-sky-2 lrv-u-font-family-secondary lrv-u-font-size-18 lrv-u-font-weight-bold u-letter-spacing-001';

const c_span__row5_image1_title             = clonedeep( c_span );
c_span__row5_image1_title.c_span_text       = 'President & Chief Media Analyst';
c_span__row5_image1_title.c_span_classes    = 'lrv-u-display-block lrv-u-font-size-14 lrv-u-text-align-center lrv-u-text-transform-uppercase u-font-family-secondary u-color-brand-secondary-80';

const c_span__row5_image1_description          = clonedeep( c_span );
c_span__row5_image1_description.c_span_text    = 'Andrew Wallenstein has been covering the media business for nearly 25 years and and received the Luminary Award for Career Achievement from the Los Angeles Press Club in 2017.';
c_span__row5_image1_description.c_span_classes = 'lrv-u-display-block lrv-u-font-size-14 lrv-u-text-align-center u-font-family-secondary u-color-brand-secondary-80';

// Team Member 2
const c_lazy_image__row5_image2                 = clonedeep( c_lazy_image );
c_lazy_image__row5_image2.c_lazy_image_src_url  = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/marketing-image-bridge-gavin.png'
c_lazy_image__row5_image2.c_lazy_image_classes  = c_lazy_image__row5_image2.c_lazy_image_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );
c_lazy_image__row5_image2.c_lazy_image_classes += ' u-width-50p@tablet lrv-a-hidden@mobile-max ';

const c_span__row5_image2_name              = clonedeep( c_span );
c_span__row5_image2_name.c_span_text        = 'Gavin Bridge';
c_span__row5_image2_name.c_span_classes     = 'u-border-color-brand-secondary-40@mobile-max lrv-u-border-t-1@mobile-max lrv-u-display-block lrv-u-font-family-secondary lrv-u-padding-t-1 lrv-u-text-align-center u-border-color-pale-sky-2 lrv-u-font-family-secondary lrv-u-font-size-18 lrv-u-font-weight-bold u-letter-spacing-001';

const c_span__row5_image2_title             = clonedeep( c_span );
c_span__row5_image2_title.c_span_text       = 'Senior Media Analyst';
c_span__row5_image2_title.c_span_classes    = 'lrv-u-display-block lrv-u-font-size-14 lrv-u-text-align-center lrv-u-text-transform-uppercase u-font-family-secondary u-color-brand-secondary-80 u-color-brand-secondary-80';

const c_span__row5_image2_description       = clonedeep( c_span );
c_span__row5_image2_description.c_span_text = 'Gavin Bridge is a veteran of market research having spent the past decade at Ipsos, LRW and GfK MRI, where he assisted TV networks, studios and streaming services with better understanding the needs of their audiences.';
c_span__row5_image2_description.c_span_classes = 'lrv-u-display-block lrv-u-font-size-14 lrv-u-text-align-center u-font-family-secondary u-color-brand-secondary-80';

// Team Member 3
const c_lazy_image__row5_image3                 = clonedeep( c_lazy_image );
c_lazy_image__row5_image3.c_lazy_image_src_url  = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/marketing-image-tran-kevin.png'
c_lazy_image__row5_image3.c_lazy_image_classes  = c_lazy_image__row5_image3.c_lazy_image_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );
c_lazy_image__row5_image3.c_lazy_image_classes += ' u-width-50p@tablet lrv-a-hidden@mobile-max ';

const c_span__row5_image3_name              = clonedeep( c_span );
c_span__row5_image3_name.c_span_text        = 'Kevin Tran';
c_span__row5_image3_name.c_span_classes     = 'u-border-color-brand-secondary-40@mobile-max lrv-u-border-t-1@mobile-max lrv-u-display-block lrv-u-font-family-secondary lrv-u-padding-t-1 lrv-u-text-align-center u-border-color-pale-sky-2 lrv-u-font-family-secondary lrv-u-font-size-18 lrv-u-font-weight-bold u-letter-spacing-001';

const c_span__row5_image3_title             = clonedeep( c_span );
c_span__row5_image3_title.c_span_text       = 'Media Analyst';
c_span__row5_image3_title.c_span_classes    = 'lrv-u-display-block lrv-u-font-size-14 lrv-u-text-align-center lrv-u-text-transform-uppercase u-font-family-secondary u-color-brand-secondary-80';

const c_span__row5_image3_description       = clonedeep( c_span );
c_span__row5_image3_description.c_span_text = 'Kevin Tran worked for Business Insider Intelligence, the paid research unit of Business Insider, where his coverage focused on how digital platforms were affecting traditional media.';
c_span__row5_image3_description.c_span_classes = 'lrv-u-display-block lrv-u-font-size-14 lrv-u-text-align-center u-font-family-secondary u-color-brand-secondary-80';

// Team Member 4
const c_lazy_image__row5_image4                 = clonedeep( c_lazy_image );
c_lazy_image__row5_image4.c_lazy_image_src_url  = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/marketing-image-eriksen-kaare.png'
c_lazy_image__row5_image4.c_lazy_image_classes  = c_lazy_image__row5_image4.c_lazy_image_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );
c_lazy_image__row5_image4.c_lazy_image_classes += ' u-width-50p@tablet lrv-a-hidden@mobile-max ';

const c_span__row5_image4_name              = clonedeep( c_span );
c_span__row5_image4_name.c_span_text        = 'Kaare Eriksen';
c_span__row5_image4_name.c_span_classes     = 'u-border-color-brand-secondary-40@mobile-max lrv-u-border-t-1@mobile-max lrv-u-display-block lrv-u-font-family-secondary lrv-u-padding-t-1 lrv-u-text-align-center u-border-color-pale-sky-2 lrv-u-font-family-secondary lrv-u-font-size-18 lrv-u-font-weight-bold u-letter-spacing-001';

const c_span__row5_image4_title             = clonedeep( c_span );
c_span__row5_image4_title.c_span_text       = 'Information Editor';
c_span__row5_image4_title.c_span_classes    = 'lrv-u-display-block lrv-u-font-size-14 lrv-u-text-align-center lrv-u-text-transform-uppercase u-font-family-secondary u-color-brand-secondary-80';

const c_span__row5_image4_description       = clonedeep( c_span );
c_span__row5_image4_description.c_span_text = 'Kaare Eriksen was previously a research coordinator for the past two years with Penske Media\'s Variety Insight division covering television in the U.K. and Canada. ';
c_span__row5_image4_description.c_span_classes = 'lrv-u-display-block lrv-u-font-size-14 lrv-u-text-align-center u-font-family-secondary u-color-brand-secondary-80';

//Explanation
const c_span_explanation__before          = clonedeep( c_span );
c_span_explanation__before.c_span_text    = 'Here\'s What';
c_span_explanation__before.c_span_classes = 'u-color-brand-secondary-60 lrv-u-font-family-secondary lrv-u-font-size-24 u-font-size-52@tablet lrv-u-margin-tb-2 lrv-u-text-align-center lrv-u-text-transform-uppercase';

const c_icon_vip            = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' ) );
c_icon_vip.c_icon_name      = 'vip-plus';
c_icon_vip.c_icon_classes   = 'u-width-100 u-width-155@tablet u-height-24 u-height-40@tablet ';

const c_span_explanation__after          = clonedeep( c_span );
c_span_explanation__after.c_span_text    = 'Subscribers Receive:';
c_span_explanation__after.c_span_classes = 'u-color-brand-secondary-60 lrv-u-font-family-secondary lrv-u-font-size-24 u-font-size-52@tablet lrv-u-margin-tb-2 lrv-u-text-align-center lrv-u-text-transform-uppercase';

//Offer Prompt
const c_icon_prompt          = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' ) );
c_icon_prompt.c_icon_name    = 'stopwatch';
c_icon_prompt.c_icon_classes = 'u-width-25 u-height-25 a-hidden@mobile-max';

const c_span_prompt_primary           = clonedeep( c_span );
c_span_prompt_primary.c_span_text     = 'Don\'t Miss Our ';
c_span_prompt_primary.c_span_classes += ' lrv-u-padding-r-050 u-padding-t-250@tablet lrv-u-font-family-secondary lrv-u-font-size-24 lrv-u-font-weight-bold lrv-u-font-size-18@mobile-max';

const c_span_prompt_secondary           = clonedeep( c_span );
c_span_prompt_secondary.c_span_text     = 'Limited Time Offer';
c_span_prompt_secondary.c_span_classes += ' u-color-brand-vip-primary lrv-u-font-family-secondary lrv-u-font-size-24 lrv-u-font-weight-bold lrv-u-font-size-18@mobile-max';

//First Offer
const c_span_offer_title_1           = clonedeep( c_span );
c_span_offer_title_1.c_span_text    = 'Monthly';
c_span_offer_title_1.c_span_classes = 'lrv-u-font-family-secondary lrv-u-font-size-24 lrv-u-font-weight-bold lrv-u-display-block lrv-u-text-align-center lrv-u-text-transform-uppercase';

const c_span_offer_cost_1           = clonedeep(c_span );
c_span_offer_cost_1.c_span_text     = '$29.99';
c_span_offer_cost_1.c_span_classes  = 'u-color-brand-vip-primary lrv-u-font-family-secondary lrv-u-font-size-24 lrv-u-font-weight-bold lrv-u-display-block lrv-u-text-align-center';

const c_span_offer_discount_1       = clonedeep(c_span );
c_span_offer_discount_1.c_span_text = 'Save over 40%';
c_span_offer_discount_1.c_span_classes = 'lrv-u-font-family-secondary lrv-u-font-size-24 lrv-u-font-weight-bold lrv-u-display-block lrv-u-text-align-center';

const c_button_offer_1         = clonedeep( c_button );
c_button_offer_1.c_button_text = 'Subscribe Now';
c_button_offer_1.c_button_url  = '#';

//Second Offer
const c_lazy_image_banner_2                 = clonedeep( c_lazy_image );
c_lazy_image_banner_2.c_lazy_image_src_url  = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/print-plus-shop-best-deal.png';
c_lazy_image_banner_2.c_lazy_image_classes += ' u-width-270 lrv-u-background-color-transparent a-pull-up-item a-pull-3 ';

const c_lazy_image_2                = clonedeep( c_lazy_image );
c_lazy_image_2.c_lazy_image_src_url = '/wp-content/themes/vip/pmc-variety-2020/assets/public/images/shop-page-vip-offer-image.png';
c_lazy_image_2.c_lazy_image_classes = c_lazy_image_2.c_lazy_image_classes.replace( 'lrv-u-background-color-grey-lightest', 'lrv-u-background-color-transparent' );

const c_span_offer_title_2       = clonedeep(c_span );
c_span_offer_title_2.c_span_text = 'Annual';
c_span_offer_title_2.c_span_classes = 'lrv-u-font-family-secondary lrv-u-font-size-24 lrv-u-font-weight-bold lrv-u-display-block lrv-u-text-align-center lrv-u-text-transform-uppercase ';

const c_span_offer_cost_2           = clonedeep(c_span );
c_span_offer_cost_2.c_span_text     = '$299 + 1 month free';
c_span_offer_cost_2.c_span_classes  = 'lrv-u-display-block lrv-u-font-family-secondary lrv-u-font-size-24 lrv-u-font-weight-bold lrv-u-text-align-center u-color-brand-vip-primary';

const c_span_offer_discount_2       = clonedeep(c_span );
c_span_offer_discount_2.c_span_text = 'Save over 50$';
c_span_offer_discount_2.c_span_classes = 'lrv-u-display-block lrv-u-font-family-secondary lrv-u-font-size-24 lrv-u-font-weight-bold lrv-u-text-align-center';

const c_button_offer_2         = clonedeep( c_button );
c_button_offer_2.c_button_url  = "";
c_button_offer_2.c_button_text = 'Subscribe Now';

//Third Offer
const c_span_offer_title_3           = clonedeep(c_span );
c_span_offer_title_3.c_span_text     = 'Corporate Subscription';
c_span_offer_title_3.c_span_classes  = 'lrv-u-font-family-secondary lrv-u-font-size-24 lrv-u-font-weight-bold lrv-u-display-block lrv-u-text-align-center lrv-u-text-transform-uppercase u-padding-b-250 u-padding-b-450@tablet';

const c_button_offer_3         = clonedeep( c_button );
c_button_offer_3.c_button_text = 'Learn More';
c_button_offer_3.c_button_url  = '/vip-corporate-subscriptions/';

module.exports = {
	marketing_landing__explanation_classes: 'lrv-u-padding-tb-2 lrv-u-text-align-center',
	marketing_landing__message_column_classes: 'u-padding-lr-1@tablet u-padding-lr-2@desktop-xl lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-center ',
	c_span: c_span,
	marketing_landing__row1_classes: 'lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-u-background-color-white',
	marketing_landing__row1_message_classes: 'u-padding-lr-1@tablet u-padding-lr-2@desktop-xl u-width-40p@tablet u-background-color-accent-b lrv-u-color-white lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-center lrv-u-align-items-center lrv-u-order-100@mobile-max lrv-u-padding-tb-2 u-padding-lr-225@mobile-max ',
	marketing_landing__row1_video_classes: 'lrv-u-flex lrv-u-flex-grow-1',
	c_span__row1_message: c_span__row1_message,
	c_span__row1_information: c_span__row1_information,
	c_button__row1_button: c_button__row1_button,
    c_span__row1_link: c_span__row1_link,
	marketing_landing__col2_classes: 'lrv-u-padding-lr-2 lrv-u-padding-tb-2',
	marketing_landing__row2_classes: 'u-padding-lr-3@tablet lrv-u-flex lrv-u-flex-grow-1 lrv-u-flex-direction-column@mobile-max lrv-u-background-color-white u-margin-lr-1@mobile-max lrv-u-order-100@mobile-max lrv-u-margin-b-2',
	marketing_landing__row2_message_classes: 'u-padding-lr-1@tablet u-padding-lr-2@desktop-xl lrv-u-color-black lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-center lrv-u-align-items-center lrv-u-height-100p lrv-u-order-100@mobile-max a-pull-2 a-pull-up-item@mobile-max ',
	marketing_landing__row2_image_classes: 'u-padding-lr-3@tablet',
	c_lazy_image__row2: c_lazy_image__row2,
	c_span__row2_number: c_span__row2_number,
	c_span__row2_information: c_span__row2_information,
	c_span__row2_list__item1: c_span__row2_list__item1,
	c_link__row2_list__item2: c_link__row2_list__item2,
	c_span__row2_list__item2: c_span__row2_list__item2,
	marketing_landing__row3_classes: 'lrv-u-flex lrv-u-flex-grow-1 lrv-u-flex-direction-column@mobile-max lrv-u-background-color-white u-margin-lr-1@mobile-max u-background-image-slash@tablet u-border-t-6@tablet u-border-b-6@tablet u-border-color-brand-secondary-40',
	marketing_landing__row3_message_classes: 'lrv-u-color-black lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-center lrv-u-align-items-center lrv-u-height-100p a-pull-1 a-pull-up-item@mobile-max lrv-u-margin-tb-1 ',
	marketing_landing__row3_image_classes: 'u-padding-lr-3@tablet a-pull-3@tablet a-pull-down-item a-pull-up-item lrv-u-flex-shrink-0 ',
	c_lazy_image__row3: c_lazy_image__row3,
	c_span__row3_number: c_span__row3_number,
	c_span__row3_information: c_span__row3_information,
	o_checks_list__row3: o_checks_list__row3,
	marketing_landing__row4_classes: ' lrv-u-flex lrv-u-flex-grow-1 lrv-u-flex-direction-column@mobile-max u-grid-gap-0 lrv-u-background-color-white  u-margin-lr-1@mobile-max lrv-u-order-100@mobile-max u-padding-t-450@tablet lrv-u-margin-tb-2',
	marketing_landing__row4_message_classes: 'u-padding-t-450@tablet u-width-50p@tablet lrv-u-color-black lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-center lrv-u-align-items-center lrv-u-height-100p lrv-u-order-100@mobile-max a-pull-1 a-pull-up-item@mobile-max ',
	marketing_landing__row4_image_classes: 'u-padding-lr-3@tablet',
	c_lazy_image__row4: c_lazy_image__row4,
	c_span__row4_number: c_span__row4_number,
	c_span__row4_message: c_span__row4_message,
	c_span__row4_information: c_span__row4_information,
	marketing_landing__row5_classes: 'u-border-t-6@tablet u-border-color-brand-secondary-40 u-padding-lr-775@tablet ' ,
	marketing_landing__row5_heading_classes: 'u-padding-lr-3@tablet  ' ,
	marketing_landing__row5_grid_classes: 'u-padding-lr-3@tablet lrv-a-grid lrv-a-cols4@tablet lrv-u-background-color-white lrv-u-margin-lr-auto u-margin-lr-1@mobile-max lrv-u-padding-tb-2 u-margin-t-2  ',
	marketing_landing__team_member_classes: 'lrv-u-align-items-center lrv-u-flex lrv-u-flex-direction-column ',
	c_span__row5_message: c_span__row5_message,
	c_lazy_image__row5_image1: c_lazy_image__row5_image1,
	c_span__row5_image1_name: c_span__row5_image1_name,
	c_lazy_image__row5_image_divider: c_lazy_image__row5_image_divider,
	c_span__row5_image1_title: c_span__row5_image1_title,
	c_span__row5_image1_description: c_span__row5_image1_description,
	c_lazy_image__row5_image2: c_lazy_image__row5_image2,
	c_span__row5_image2_name: c_span__row5_image2_name,
	c_span__row5_image2_title: c_span__row5_image2_title,
	c_span__row5_image2_description: c_span__row5_image2_description,
	c_lazy_image__row5_image3: c_lazy_image__row5_image3,
	c_span__row5_image3_name: c_span__row5_image3_name,
	c_span__row5_image3_title: c_span__row5_image3_title,
	c_span__row5_image3_description: c_span__row5_image3_description,
	c_lazy_image__row5_image4: c_lazy_image__row5_image4,
	c_span__row5_image4_name: c_span__row5_image4_name,
	c_span__row5_image4_title: c_span__row5_image4_title,
	c_span__row5_image4_description: c_span__row5_image4_description,
	c_span_explanation__before: c_span_explanation__before,
	c_icon_vip: c_icon_vip,
	c_span_explanation__after: c_span_explanation__after,
	c_icon_prompt: c_icon_prompt,
	c_span_prompt_primary: c_span_prompt_primary,
	c_span_prompt_secondary: c_span_prompt_secondary,
	c_lazy_image: c_lazy_image,
	c_span__row5: c_span__row5,
	marketing_landing__vip_offer_classes: 'lrv-u-text-align-center u-background-image-slash u-border-color-brand-secondary-40 u-border-t-6 lrv-u-margin-t-2 u-padding-a-1@mobile-max lrv-u-padding-b-2',
	marketing_landing__vip_offer_prompt_grid_classes: 'lrv-a-cols3@tablet lrv-a-grid lrv-u-margin-lr-auto u-align-items-flex-end u-max-width-1160 u-justify-content-center@mobile-max',
	marketing_landing__vip_offer_prompt_grid__col_classes: 'lrv-u-padding-t-2 u-width-350 lrv-u-margin-lr-auto lrv-u-align-items-center lrv-u-background-color-white u-height-250 lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-space-evenly lrv-u-padding-a-2 u-box-shadow-medium ',
	marketing_landing__vip_offer_prompt_classes: 'lrv-u-padding-tb-2',
	c_span_offer_title_1: c_span_offer_title_1,
	c_span_offer_cost_1: c_span_offer_cost_1,
	c_span_offer_discount_1: c_span_offer_discount_1,
	c_button_offer_1: c_button_offer_1,
	c_lazy_image_banner_2: c_lazy_image_banner_2,
	c_span_offer_title_2: c_span_offer_title_2,
	c_span_offer_cost_2: c_span_offer_cost_2,
	c_span_offer_discount_2: c_span_offer_discount_2,
	c_button_offer_2: c_button_offer_2,
	c_span_offer_title_3: c_span_offer_title_3,
	c_button_offer_3: c_button_offer_3
};
