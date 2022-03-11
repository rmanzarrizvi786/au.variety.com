const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.variety-vip.js' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_video_card_prototype = require( '../../objects/o-video-card/o-video-card.slider.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.prototype.js' );
const o_more_link = clonedeep( o_more_link_prototype );
const view_all_link = clonedeep( o_more_link_prototype );

o_more_from_heading.c_v_icon = null;
o_more_from_heading.o_more_from_heading_classes += '  a-hidden@tablet';
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'lrv-u-padding-tb-050', 'lrv-u-padding-t-025 lrv-u-padding-b-1' );
o_more_from_heading.c_heading.c_heading_text = 'Video';
o_more_from_heading.c_heading.c_heading_classes = o_more_from_heading.c_heading.c_heading_classes.replace( 'u-letter-spacing-021', 'u-letter-spacing-040@mobile-max' );
o_more_from_heading.c_heading.c_heading_classes += ' u-font-family-secondary@tablet u-margin-b-075@tablet';

c_heading.c_heading_classes = 'a-hidden@mobile-max lrv-u-font-family-primary u-font-size-30 u-font-weight-medium u-font-family-secondary@tablet u-font-size-32@tablet u-font-weight-bold@tablet';
c_heading.c_heading_text = 'Exclusive Videos';

o_more_link.o_more_link_classes = 'lrv-u-text-align-right lrv-u-margin-t-1 u-margin-r-150 a-hidden@tablet';
o_more_link.c_link.c_link_text = 'More Videos';
o_more_link.c_link.c_link_url = '/vip-video/';

view_all_link.o_more_link_classes = 'lrv-u-flex a-hidden@mobile-max';
view_all_link.c_link.c_link_text = 'View All';

const video_items = [
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
];

module.exports = {
	video_carousel_classes: 'u-border-t-6 u-border-b-6 u-border-color-brand-secondary-50 u-padding-b-175 u-padding-t-150@tablet u-padding-b-150@tablet u-background-image-slash@tablet',
	video_inner_classes: 'js-Flickity--isFreeScroll js-Flickity--hide-nav@mobile-max js-Flickity--isWrapAround js-Flickity--sideShrink@tablet',
	video_item_classes: 'lrv-u-margin-lr-050 u-margin-r-250@tablet u-width-215@mobile-max u-width-665@tablet u-width-800@desktop-xl a-scale-110@tablet:hover u-padding-t-1@tablet u-padding-b-150@tablet',
	video_carousel_header_classes: 'lrv-u-flex@tablet lrv-u-align-items-center lrv-u-justify-content-space-between u-max-width-800 u-margin-t-025 u-margin-t-150@tablet',
	o_more_from_heading,
	video_items,
	c_heading,
	o_more_link,
	view_all_link,
};
