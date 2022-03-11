const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' );
const c_heading = clonedeep( c_heading_prototype );

c_heading.c_heading_classes = 'a-font-accent-m lrv-u-padding-tb-075 u-padding-t-025@tablet u-letter-spacing-015-important u-letter-spacing-025@desktop-xl';
c_heading.c_heading_text = 'Documentary Video';

const o_video_card_prototype = require( '../../objects/o-video-card/o-video-card.prototype.js' );
const o_video_card = clonedeep( o_video_card_prototype );

o_video_card.c_heading.c_heading_classes = 'a-font-primary-regular-l u-font-size-32@tablet lrv-u-font-size-22 lrv-u-line-height-small';
o_video_card.c_heading.c_heading_link_classes = 'u-color-brand-secondary-50:hover';
o_video_card.o_indicator.c_span.c_span_link_classes = 'u-color-black:hover';
o_video_card.o_indicator.c_span.c_span_text = 'Doc Dreams';
o_video_card.o_indicator.o_indicator_classes = 'lrv-u-margin-t-00 lrv-u-margin-b-1';
o_video_card.o_indicator.c_span.c_span_classes = 'a-font-secondary-bold-xs lrv-u-text-transform-uppercase u-letter-spacing-012 u-font-size-11';
o_video_card.c_span.c_span_classes = 'a-font-secondary-bold-xs lrv-u-text-transform-uppercase u-letter-spacing-012 u-font-size-11 lrv-u-margin-tb-1';
o_video_card.o_video_card_classes = '';
o_video_card.c_play_icon.c_play_badge_classes = 'lrv-a-glue a-glue--a-50p u-width-30 u-height-30 u-width-70@tablet u-height-70@tablet u-transform-translate-a-n50p is-to-be-hidden';
o_video_card.o_video_card_meta_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-width-100p lrv-u-padding-tb-00 u-position-relative';
o_video_card.c_span.c_span_classes = 'a-font-secondary-bold-xs lrv-u-text-transform-uppercase u-letter-spacing-012 u-font-size-11 lrv-u-margin-t-050 lrv-u-margin-b-1';

const o_video_card_top = clonedeep( o_video_card );

o_video_card_top.o_video_card_classes = 'lrv-a-grid a-cols4@desktop lrv-u-padding-tb-1 lrv-u-border-t-1 lrv-u-border-b-1 u-border-color-link-water lrv-u-margin-b-1';
o_video_card_top.o_video_card_crop_class += ' lrv-a-span3@tablet';

const o_video_card_list_prototype = require( '../../objects/o-video-card-list/o-video-card-list.prototype.js' );
const o_video_card_list = clonedeep( o_video_card_list_prototype );

o_video_card.o_video_card_classes = 'u-border-b-1@mobile-max u-border-color-link-water';
o_video_card.o_indicator = false;
o_video_card.c_span = false;
o_video_card.c_heading.c_heading_classes = 'a-font-secondary-bold-s u-line-height-130';
o_video_card.o_video_card_meta_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-width-100p u-padding-lr-075@mobile-max lrv-u-padding-tb-050 u-position-relative';
o_video_card.c_play_icon.c_play_badge_classes = 'lrv-a-glue a-glue--a-50p lrv-u-display-block u-width-30 u-height-30 u-width-50@desktop u-height-50@desktop u-transform-translate-a-n50p';

const o_video_card_list_items = [
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card
];

o_video_card_list.o_video_card_list_classes = 'lrv-a-grid lrv-a-cols4@tablet u-border-b-1@tablet u-border-color-link-water lrv-u-margin-b-050 lrv-u-padding-b-050';
o_video_card_list.o_video_card_list_items = o_video_card_list_items;

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.prototype.js' );
const { o_more_link_classes } = require('../../objects/o-more-link/o-more-link.prototype.js');
const o_more_link = clonedeep( o_more_link_prototype );

o_more_link.c_link.c_link_text = 'More Video';
o_more_link.o_more_link_classes = 'lrv-u-flex';
o_more_link.c_link.c_link_classes = 'lrv-a-unstyle-link lrv-u-margin-l-auto lrv-u-text-transform-uppercase lrv-u-font-weight-bold u-font-size-11 u-color-pale-sky-2 lrv-u-font-family-secondary u-letter-spacing-012 lrv-a-icon-after a-icon-long-right-arrow-blue';

module.exports = {
	docs_video_outer_classes: 'lrv-u-border-t-3 lrv-u-padding-t-075@mobile-max lrv-u-margin-b-2',
	docs_video_classes: '',
	docs_video_inner_classes: '',
	c_heading,
	o_video_card_top,
	o_video_card_list,
	o_more_link,
};