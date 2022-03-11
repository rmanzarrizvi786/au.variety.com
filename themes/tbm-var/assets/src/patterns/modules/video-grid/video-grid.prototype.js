const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( '../../objects/o-video-card/o-video-card.small.grid.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.prototype.js' );
const o_more_link = clonedeep( o_more_link_prototype );

const {
	c_heading,
} = o_video_card;

o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( 'u-width-125', 'lrv-u-width-100p' );
o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( 'lrv-u-flex@tablet', 'lrv-u-flex' );
o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( 'u-padding-t-125@tablet', 'lrv-u-padding-t-050@tablet' );
o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( 'u-border-t-1@mobile-max', 'lrv-u-border-b-1' );
o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( 'u-margin-lr-075@mobile-max', '' );
o_video_card.o_video_card_classes += ' lrv-u-padding-b-1';

o_video_card.o_video_card_meta_classes = o_video_card.o_video_card_meta_classes.replace( 'u-margin-t-050@mobile-max', '' );
o_video_card.o_video_card_meta_classes = o_video_card.o_video_card_meta_classes.replace( 'u-justify-content-space-between@tablet', 'u-justify-content-space-between@desktop-xl-max' );
o_video_card.o_video_card_meta_classes = o_video_card.o_video_card_meta_classes.replace( 'u-margin-l-1@tablet', 'u-margin-l-050@tablet' );
o_video_card.o_video_card_meta_classes += ' u-order-n1@mobile-max u-margin-l-1@desktop-xl';

o_video_card.o_video_card_crop_class = o_video_card.o_video_card_crop_class.replace( 'u-width-175@tablet', 'u-width-165@tablet' );
o_video_card.o_video_card_crop_class += ' u-width-160';
o_video_card.o_video_card_image_url = 'https://source.unsplash.com/random/245x138';

const o_video_card_first = clonedeep( o_video_card );
const o_video_card_last = clonedeep( o_video_card );
o_video_card_first.o_video_card_classes = o_video_card.o_video_card_classes.replace( ' u-padding-t-075 ', ' lrv-u-padding-t-025 ' );
o_video_card.o_video_card_classes = o_video_card.o_video_card_classes.replace( ' u-padding-t-075 ', ' ' );
o_video_card_last.o_video_card_classes = o_video_card_last.o_video_card_classes.replace( 'lrv-u-border-b-1', 'u-border-b-1@tablet' );

c_heading.c_heading_text = 'Oscars Best Actor 2020: Who Will Win?';
c_heading.c_heading_classes = 'lrv-u-color-white lrv-u-font-weight-normal lrv-u-font-family-secondary u-font-size-13 u-font-size-14@desktop-xl u-line-height-120 u-order-n1@mobile-max u-margin-b-050@desktop u-max-height-48em a-truncate-ellipsis';

o_more_link.o_more_link_classes = 'lrv-u-text-align-right u-margin-t-025 lrv-u-margin-lr-auto lrv-u-padding-b-2 u-margin-t-050@desktop-xl ';
o_more_link.c_link.c_link_text = 'More Stories';
o_more_link.c_link.c_link_url = '#page_2';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'a-icon-long-right-arrow', 'a-icon-long-right-arrow-blue' );
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'u-color-brand-secondary-50', 'lrv-u-color-white' );
o_more_link.c_link.c_link_classes += ' u-color-brand-primary:hover';

const video_items = [
	o_video_card_first,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card_last,
];

module.exports = {
	video_grid_wrapper_classes: 'u-background-color-picked-bluewood lrv-u-padding-b-1 u-padding-b-025@desktop-xl',
	video_grid_classes: 'a-cols3@tablet u-grid-gap-1 u-grid-gap-050x2@tablet u-grid-gap-050x175@desktop u-background-color-picked-bluewood u-border-t-6 u-border-color-pale-sky-2 lrv-u-padding-t-050',
	play_in_place: false,
	video_items,
	o_more_link,
};
