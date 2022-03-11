const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' );
const c_heading = clonedeep( c_heading_prototype );

const video_showcase_prototype = require( '../video-showcase/video-showcase.prototype' );
const video_showcase = clonedeep( video_showcase_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.prototype' );
const o_more_link = clonedeep( o_more_link_prototype );

const o_video_card_related_prorotype = require( '../../objects/o-video-card/o-video-card.related' );
const o_video_card_related = clonedeep( o_video_card_related_prorotype );
o_video_card_related.o_indicator = false;
o_video_card_related.c_play_icon = false;
const {
	related_videos,
	o_video_card,
} = video_showcase;

c_heading.c_heading_text = 'Video';
c_heading.c_heading_classes = 'lrv-u-color-white u-font-family-accent lrv-u-font-weight-normal lrv-u-text-align-center@mobile-max lrv-u-text-transform-uppercase u-font-size-30 u-font-size-52@tablet u-letter-spacing-040 u-margin-t-025 lrv-u-margin-b-050 u-line-height-110@tablet';

o_more_link.o_more_link_classes += ' lrv-u-text-align-right lrv-u-padding-tb-050 u-padding-b-075@tablet';
o_more_link.c_link.c_link_text = 'More Video';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'u-color-brand-secondary-50', 'lrv-u-color-white  u-color-brand-primary:hover' );
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'a-icon-long-right-arrow', 'a-icon-long-right-arrow-blue' );

video_showcase.video_header_videos_classes = video_showcase.video_header_videos_classes.replace( 'u-margin-t-2@tablet', 'u-margin-t-125@tablet' );
video_showcase.video_header_videos_classes = video_showcase.video_header_videos_classes.replace( 'u-padding-b-125@tablet', 'u-padding-b-050@tablet' );
video_showcase.video_header_videos_classes += ' u-margin-lr-n050@mobile-max lrv-a-grid lrv-a-cols4@tablet';
video_showcase.video_header_videos_classes = video_showcase.video_header_videos_classes.replace( 'lrv-u-flex@tablet', '');
video_showcase.video_header_single_player_classes = 'lrv-a-span3@tablet';

related_videos.o_more_from_heading.c_heading.c_heading_text = 'Featured Video';
related_videos.o_more_from_heading.c_heading.c_heading_classes = related_videos.o_more_from_heading.c_heading.c_heading_classes.replace( 'u-font-size-21@tablet', 'lrv-u-font-size-18@tablet' );
related_videos.o_more_from_heading.c_heading.c_heading_classes += ' u-font-size-21@desktop-xl u-margin-b-050@desktop-xl';
related_videos.o_video_card_list.o_video_card_list_classes = related_videos.o_video_card_list.o_video_card_list_classes.replace( 'u-border-b-1@mobile-max', '' );
related_videos.o_video_card_list.o_video_card_list_classes = related_videos.o_video_card_list.o_video_card_list_classes.replace( 'u-margin-b-125@mobile-max', '' );
related_videos.related_videos_wrap_classes += ' u-width-190@tablet u-width-250@desktop-xl';
related_videos.o_video_card_list.o_video_card_list_item_classes += ' u-margin-b-025@tablet u-margin-t-025@desktop-xl';

o_video_card_related.o_video_card_classes = o_video_card_related.o_video_card_classes.replace( 'u-padding-t-125@tablet', 'lrv-u-padding-t-050@tablet' );
o_video_card_related.o_video_card_classes = o_video_card_related.o_video_card_classes.replace( 'u-width-175@mobile-max', 'u-width-125' );
o_video_card_related.o_video_card_classes += ' lrv-u-flex-direction-column';
o_video_card_related.o_video_card_crop_class = o_video_card_related.o_video_card_crop_class.replace( 'u-width-175@tablet', '' );

o_video_card_related.o_video_card_image_url = 'https://source.unsplash.com/random/265x150';
o_video_card_related.o_video_card_meta_classes = o_video_card_related.o_video_card_meta_classes.replace( 'u-margin-l-050@tablet', '' );
o_video_card_related.c_heading.c_heading_classes = o_video_card_related.c_heading.c_heading_classes.replace( 'u-line-height-120', '' );
o_video_card_related.c_heading.c_heading_classes += ' u-margin-t-050@tablet u-margin-b-050@desktop-xl';
o_video_card_related.c_dek = false;
related_videos.o_video_card_list.o_video_card_list_items = [
	o_video_card_related,
	o_video_card_related,
	o_video_card_related,
	o_video_card_related,
	o_video_card_related,
	o_video_card_related,
	o_video_card_related,
	o_video_card_related,
];
o_video_card.c_dek = false;
o_video_card.o_video_card_link_classes += ' lrv-u-display-block lrv-u-margin-r-1@tablet';
o_video_card.c_heading.c_heading_classes = o_video_card.c_heading.c_heading_classes.replace( 'lrv-u-font-size-24', 'u-font-size-22' );
o_video_card.c_span.c_span_classes = o_video_card.c_span.c_span_classes.replace( 'lrv-u-font-size-14', 'lrv-u-font-size-10' );
o_video_card.c_span.c_span_classes += ' lrv-u-font-size-14@tablet';
o_video_card.o_indicator = false;
o_video_card.o_indicator.o_indicator_classes += ' lrv-u-display-none';
o_video_card.o_video_card_meta_classes += ' u-margin-t-050@desktop-xl';

module.exports = {
	homepage_video_outer_classes: 'lrv-u-margin-t-1',
	homepage_video_classes: 'u-max-width-100vw',
	homepage_video_inner_classes: 'u-background-color-picked-bluewood u-border-t-6 u-border-color-pale-sky-2 lrv-u-padding-lr-1',
	c_heading,
	video_showcase,
	o_more_link,
};
