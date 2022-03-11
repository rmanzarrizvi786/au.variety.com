const clonedeep = require( 'lodash.clonedeep' );

const o_more_link = clonedeep( require( '../../objects/o-more-link/o-more-link.prototype' ) );
o_more_link.o_more_link_classes   = 'lrv-u-padding-tb-050 u-colors-map-accent-b-80 lrv-u-text-transform-uppercase u-color-pale-sky-2 u-letter-spacing-2';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'a-icon-long-right-arrow', 'a-icon-long-right-arrow-blue' );

const o_checks_list                       = clonedeep( require( '../../objects/o-checks-list/o-checks-list.prototype' ) );
o_checks_list.o_checks_list_classes       = 'lrv-u-font-family-secondary u-padding-lr-0 lrv-u-font-size-18 lrv-u-font-size-13@mobile-max lrv-u-padding-tb-050 u-border-none u-list-style-type-square';
o_checks_list.o_checks_list_items_classes = 'u-padding-tb-0 lrv-u-font-size-12@mobile-max ';

const o_check_list_item                = clonedeep( require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' ) );
o_checks_list.o_checks_list_text_items = [
	o_check_list_item,
	o_check_list_item,
	o_check_list_item,
];

const c_title                = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' ) );
c_title.c_title_text         = 'Print Plus';
c_title.c_title_classes      = 'lrv-u-font-size-14 lrv-u-padding-tb-025 lrv-u-font-size-26@mobile-max lrv-u-font-size-32@desktop lrv-u-font-family-primary lrv-u-display-block u-font-weight-normal@mobile-max lrv-u-line-height-small lrv-u-margin-b-050';
c_title.c_title_link_classes = 'lrv-a-unstyle-link u-color-brand-primary:hover';
c_title.c_title_url          = '';

const c_span_label          = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_span_label.c_span_text    = 'Print Plus';
c_span_label.c_span_classes = 'u-font-family-basic lrv-u-font-size-12 lrv-u-font-size-13@mobile-max u-color-pale-sky-2 u-letter-spacing-2 u-font-weight-normal lrv-u-text-transform-uppercase';
c_span_label.c_span_url     = '';

const c_dek         = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' ) );
c_dek.c_dek_text    = '';
c_dek.c_dek_markup  = 'Lorem ipsum <i>dolor</i> sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.';
c_dek.c_dek_classes = 'lrv-u-font-weight-light lrv-u-font-size-12@mobile-max lrv-u-font-size-18 lrv-u-margin-a-00 lrv-u-font-family-secondary';

const c_lazy_image                   = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' ) );
c_lazy_image.c_lazy_image_classes = 'lrv-u-flex-shrink-0 lrv-u-width-100p@mobile-max u-max-width-380@tablet';
c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-5x3';

module.exports = {
	print_plus_item_classes: 'lrv-u-background-color-white lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-u-margin-lr-auto lrv-u-margin-tb-1 lrv-u-padding-tb-1@mobile-max u-border-color-brand-primary u-border-t-6@mobile-max u-box-shadow-light u-max-width-940 u-padding-lr-1@mobile-max',
	print_plus_item__col2_classes: 'lrv-u-padding-lr-2 lrv-u-padding-tb-050 u-border-color-brand-primary u-border-t-6@tablet u-width-100p@tablet',
	c_link: false,
	c_title: c_title,
	c_span_label: c_span_label,
	c_dek: c_dek,
	c_lazy_image: c_lazy_image,
	o_more_link: o_more_link,
	o_checks_list: o_checks_list,
};
