const clonedeep = require( 'lodash.clonedeep' );

const o_sub_heading = clonedeep( require( '../../objects/o-sub-heading/o-sub-heading.prototype' ) );

const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.prototype' );
const o_tease_list = clonedeep( o_tease_list_prototype );

const o_tease_prototype = require( '../../objects/o-tease/o-tease.must-read-primary' );
const o_tease = clonedeep( o_tease_prototype );

const c_dek = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' ) );

const c_link = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' ) );

o_sub_heading.c_heading.c_heading_text = 'Album Reviews';
o_sub_heading.c_span.c_span_text = 'Music';
o_sub_heading.c_dek.c_dek_text = 'Who doesn\'t love new music, our critics pick the bets new albums of the moment.';
o_sub_heading.o_sub_heading_classes = 'lrv-u-padding-b-150 lrv-u-border-b-1 u-border-color-link-water lrv-u-margin-b-150';

c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-margin-a-00 lrv-u-font-size-14@tablet u-font-size-16@desktop u-line-height-140 u-line-height-normal@desktop';
c_dek.c_dek_text = 'This is what the news should sound like. The biggest stories of our time, told by the best journalists in the world. Hosted by Michael Barbaro. Twenty minutes a day, five days a week, ready by 6 a.m.';

c_link.c_link_classes += ' a-font-secondary-bold-4xs lrv-u-text-transform-uppercase u-color-action-blue u-color-action-blue:hover u-letter-spacing-2 a-separator-before lrv-u-padding-t-075 lrv-u-margin-t-075 u-order-5 lrv-u-padding-b-1@mobile-max';
c_link.c_link_text = 'Listen';

o_tease.o_tease_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-padding-b-1 u-border-color-brand-secondary-40 u-padding-b-1@tablet u-border-color-brand-secondary-40 u-border-b-1@mobile-max';
o_tease.o_tease_primary_classes = 'lrv-u-flex lrv-u-flex-direction-column';
o_tease.c_title.c_title_text = 'King Krule';
o_tease.c_title.c_title_classes = 'u-order-n1 lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-18@mobile-max lrv-u-font-size-16 lrv-u-font-size-18@desktop u-line-height-120 lrv-u-margin-b-075 u-letter-spacing-0002 lrv-u-margin-b-075';
o_tease.c_span.c_span_text = 'IndieWire';
o_tease.c_span.c_span_classes = 'lrv-u-display-block lrv-u-margin-b-025 lrv-u-text-transform-uppercase a-font-secondary-bold u-font-size-11 u-letter-spacing-2 u-margin-b-050@tablet';
o_tease.c_span.c_span_link_classes = 'u-color-black-pearl u-color-black:hover lrv-u-display-block';
o_tease.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1';
o_tease.c_lazy_image.c_lazy_image_img_classes += ' lrv-u-border-a-1 u-border-color-link-water';
o_tease.c_lazy_image.c_lazy_image_classes = 'lrv-u-margin-b-1';
o_tease.c_dek = c_dek;
o_tease.c_link = c_link;
o_tease.c_timestamp = false;

o_tease.c_lazy_image.c_lazy_image_classes = o_tease.c_lazy_image.c_lazy_image_classes.replace( 'u-margin-lr-n075@mobile-max', '' );

o_tease_list.o_tease_list_classes += ' lrv-a-grid a-cols4@tablet u-grid-gap-075@mobile-max';
o_tease_list.o_tease_list_item_classes = '';

const o_tease_last = clonedeep( o_tease );
o_tease_last.o_tease_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-padding-b-1 u-border-color-brand-secondary-40 u-padding-b-1@tablet u-margin-b-250@mobile-max';

o_tease_list.o_tease_list_items = [
	o_tease,
	o_tease,
	o_tease,
	o_tease_last
];

module.exports = {
	wth_reviews_wrapper_classes: 'u-padding-b-125@tablet',
	wth_reviews_classes: '',
	wth_reviews_header_classes: 'lrv-u-flex lrv-u-flex-direction-column@mobile-max u-align-items-flex-end@tablet',
	o_sub_heading,
	o_tease_list
};
