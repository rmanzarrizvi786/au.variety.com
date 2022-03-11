const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

const o_more_link_prototype = require( '../o-more-link/o-more-link.prototype.js' );
const o_more_link = clonedeep( o_more_link_prototype );
const o_more_link_mobile = clonedeep( o_more_link_prototype );

const o_video_card_primary_prototype = require( '../o-video-card/o-video-card.primary.js' );
const o_video_card_prototype = require( '../o-video-card/o-video-card.small.grid.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const o_video_card_primary = clonedeep( o_video_card_primary_prototype );

c_heading.c_heading_text = 'Popular on Variety';
c_heading.c_heading_classes = 'lrv-u-color-white lrv-u-text-transform-uppercase u-font-size-30 u-letter-spacing-040 u-font-family-accent lrv-u-padding-t-025 u-padding-b-1@tablet lrv-u-margin-b-050@mobile-max u-font-size-42@tablet';

o_more_link.o_more_link_classes = 'a-hidden@mobile-max lrv-u-color-white u-color-brand-primary:hover';
o_more_link.c_link.c_link_text = 'View All';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'a-icon-long-right-arrow', 'a-icon-long-right-arrow-blue' );

o_more_link_mobile.c_link.c_link_text = 'More Popular on Variety';
o_more_link_mobile.o_more_link_classes = 'a-hidden@tablet lrv-u-color-white lrv-u-margin-lr-050 lrv-u-margin-b-050 u-margin-t-150 lrv-u-padding-tb-050 lrv-u-text-align-right lrv-u-border-t-1 u-border-color-pale-sky-2 u-color-brand-primary:hover';
o_more_link_mobile.c_link.c_link_classes = o_more_link_mobile.c_link.c_link_classes.replace( 'a-icon-long-right-arrow', 'a-icon-long-right-arrow-blue' );

const o_video_card_bottom = clonedeep( o_video_card );

o_video_card_bottom.o_video_card_classes += ' u-border-t-1@tablet';

const o_popular_videos_items = [
	o_video_card,
	o_video_card,
	o_video_card_bottom,
	o_video_card_bottom,
];

module.exports = {
	o_popular_videos_classes: 'u-background-color-accent-c-100@mobile-max u-margin-tb-125',
	o_popular_videos_inner_classes: 'u-background-color-picked-bluewood u-border-t-6 u-border-color-pale-sky-2 lrv-u-margin-lr-auto u-padding-b-050@tablet',
	o_popular_videos_header_classes: 'lrv-u-text-align-center@mobile-max lrv-u-flex@tablet lrv-u-align-items-center lrv-u-justify-content-space-between u-margin-lr-1@tablet u-border-b-1@tablet u-border-color-pale-sky-2 u-margin-b-050@tablet',
	o_popular_videos_primary_classes: 'lrv-u-margin-b-050 u-margin-t-075@tablet lrv-u-padding-r-1@tablet lrv-u-margin-r-1@tablet u-border-r-1@tablet u-border-color-pale-sky-2',
	o_popular_videos_secondary_classes: 'lrv-a-grid a-cols2@tablet a-span2@tablet u-grid-gap-0x050@tablet',
	o_popular_videos_content_classes: 'lrv-u-flex@tablet u-padding-lr-1@tablet',
	c_heading,
	o_more_link,
	o_video_card_primary,
	o_popular_videos_items,
	o_more_link_mobile,
};
