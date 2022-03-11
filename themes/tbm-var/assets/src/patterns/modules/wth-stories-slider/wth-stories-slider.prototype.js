
const clonedeep = require( 'lodash.clonedeep' );

const o_sub_heading = clonedeep( require( '../../objects/o-sub-heading/o-sub-heading.prototype' ) );

const c_dek = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' ) );

const c_link = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' ) );

const special_report_carousel_prototype = require( '../special-reports-carousel/special-reports-carousel.prototype.js' );
const special_report_carousel = clonedeep( special_report_carousel_prototype );

o_sub_heading.c_heading.c_heading_text = 'Variety Recommends';
o_sub_heading.c_span.c_span_text = 'Audiobooks';
o_sub_heading.c_dek.c_dek_text = 'Dive in to the best audiobooks for your next vacation or long car dive getting Hollywood\'s attention.';
o_sub_heading.o_sub_heading_classes = 'lrv-u-padding-b-150 lrv-u-border-b-1 u-border-color-link-water';

c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-margin-a-00 lrv-u-font-size-14@tablet u-font-size-16@desktop';
c_dek.c_dek_text = 'This is what the news should sound like. The biggest stories of our time, told by the best journalists in the world. Hosted by Michael Barbaro. Twenty minutes a day, five days a week, ready by 6 a.m.';

c_link.c_link_classes += ' a-font-secondary-bold-4xs lrv-u-text-transform-uppercase u-color-action-blue u-color-action-blue:hover u-letter-spacing-2 a-separator-before lrv-u-padding-t-075 lrv-u-margin-t-075 u-order-5';
c_link.c_link_text = 'Listen';
c_link.c_link_target_attr = false;

const o_card_prototype = require( '../../objects/o-card/o-card.story' );

const o_card = clonedeep( o_card_prototype );

const c_title = require( clonedeep( '../../components/c-title/c-title.slider' ));
c_title.c_title_text = "'Title'";
c_title.c_title_url = "#";

o_card.c_dek = c_dek;
o_card.c_link = c_link;
o_card.c_title = c_title;
o_card.external_link_url = false;

o_card.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1';
o_card.c_lazy_image.c_lazy_image_link_attr = false;
o_card.c_title.c_title_classes = 'u-order-n1 lrv-u-padding-t-1 lrv-u-font-size-18@mobile-max lrv-u-font-size-16 u-line-height-120@desktop lrv-u-font-size-18@desktop u-order-n1';
c_title.c_title_link_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold  lrv-u-display-block u-color-black-pearl:hover';
o_card.c_title.c_title_link_attr = false;

const first_o_span = clonedeep(o_card.o_span_group.o_span_group_items[0]);
const second_o_span = clonedeep(o_card.o_span_group.o_span_group_items[1]);
first_o_span.c_span_classes = 'lrv-u-text-transform-uppercase u-font-size-11 lrv-u-font-weight-bold lrv-u-font-family-secondary u-letter-spacing-012 lrv-u-margin-b-025';
first_o_span.c_span_text = 'By Michelle Obama';
second_o_span.c_span_classes = 'u-color-dark-grey lrv-u-text-transform-uppercase u-font-size-11 lrv-u-font-weight-bold lrv-u-font-family-secondary u-letter-spacing-012 lrv-u-margin-b-075';
second_o_span.c_span_text = 'Narrated by Michelle Obama';

o_card.o_span_group.o_span_group_items = [
	first_o_span,
	second_o_span
];

const o_card_first = clonedeep( o_card );
o_card_first.o_card_classes += ' lrv-u-margin-r-050';
o_card.o_card_classes += ' lrv-u-margin-lr-050';

special_report_carousel.special_report_items = null;
special_report_carousel.o_more_from_heading = null;

special_report_carousel.special_reports_carousel_classes = 'u-padding-b-150 lrv-u-margin-lr-auto';

special_report_carousel.special_report_inner_classes = 'js-Flickity--nav-top-right js-Flickity--isContained is-draggable u-padding-t-3@tablet u-padding-t-250';

special_report_carousel.special_report_item_classes = 'u-border-color-link-water lrv-u-flex u-min-height-100p';

const wth_stories_slider_items = [
	o_card_first,
	o_card,
	o_card,
	o_card,
	o_card,
];

module.exports = {
	...special_report_carousel,
	wth_stories_slider_classes: '',
	wth_stories_slider_id_attr: o_sub_heading.c_heading.c_heading_text.toLowerCase().replace(' ','-'),
	wth_stories_slider_offset_attr: false,
	wth_stories_slider_wrapper_classes: 'u-margin-b-075 ',
	o_sub_heading,
	wth_stories_slider_items,
};
