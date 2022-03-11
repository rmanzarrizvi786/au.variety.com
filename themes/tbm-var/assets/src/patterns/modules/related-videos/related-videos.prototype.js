const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.prototype.js' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const o_video_card_list_prototype = require( '../../objects/o-video-card-list/o-video-card-list.prototype.js' );
const o_video_card_list = clonedeep( o_video_card_list_prototype );

const o_video_card_prototype = require( '../../objects/o-video-card/o-video-card.related.js' );
const o_video_card = clonedeep( o_video_card_prototype );

// Clicking on the title of related videos should load the video instead of link to a page.
o_video_card.c_heading.c_heading_url = '';

const { c_heading } = o_more_from_heading;

o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'lrv-u-padding-tb-050', 'u-padding-t-050@mobile-max' );

c_heading.c_heading_text = 'Related Videos';
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'lrv-u-border-b-1', '' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'lrv-u-margin-b-1', '' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-font-size-32@tablet', 'u-font-size-21@tablet' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'lrv-u-padding-b-050', 'lrv-u-padding-b-025' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-padding-b-1@tablet', 'u-padding-b-050@tablet' );
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-margin-b-125@tablet', 'u-margin-b-025@tablet' );
c_heading.c_heading_classes += ' u-border-b-1@tablet u-border-color-pale-sky-2 lrv-u-width-100p';

o_video_card_list.o_video_card_list_classes += ' lrv-u-flex lrv-u-flex-direction-column@tablet lrv-u-width-100p u-padding-b-125 a-separator-r-1@mobile-max a-separator-b-1@tablet u-margin-b-125@mobile-max u-border-b-1@mobile-max u-border-color-pale-sky-2';
o_video_card_list.o_video_card_list_item_classes = 'u-width-44p@mobile-max lrv-u-flex-shrink-0 u-padding-lr-075@mobile-max lrv-u-height-100p u-border-color-pale-sky-2';

o_video_card_list.o_video_card_list_items = [
	clonedeep( o_video_card ),
	clonedeep( o_video_card ),
	clonedeep( o_video_card ),
	clonedeep( o_video_card ),
	clonedeep( o_video_card ),
	clonedeep( o_video_card ),
	clonedeep( o_video_card ),
	clonedeep( o_video_card ),
];

o_video_card_list.o_video_card_list_items[0].o_video_card_link_showcase_trigger_data_attr = "<div class='embed-youtube'><iframe title='Spring - Blender Open Movie' width='500' height='281' src='https://www.youtube.com/embed/WhWc3b3KhnY?feature=oembed&autoplay=1&mute=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen=''></iframe></div>";

o_video_card_list.o_video_card_list_items[1].o_video_card_link_showcase_trigger_data_attr = "<div class='embed-youtube'><iframe title='Redemption' width='500' height='281' src='https://www.youtube.com/embed/IeVK9rPkbKg?feature=oembed&autoplay=1&mute=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen=''></iframe></div>";

o_video_card_list.o_video_card_list_items[2].o_video_card_link_showcase_trigger_data_attr = "<div class='embed-youtube'><iframe title='Testing' width='500' height='281' src='https://www.youtube.com/embed/SXvQ1nK4oxk?feature=oembed&autoplay=1&mute=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen=''></iframe></div>";

o_video_card_list.o_video_card_list_items[3].o_video_card_link_showcase_trigger_data_attr = "<div class='embed-youtube'><iframe title='Testing' width='500' height='281' src='https://www.youtube.com/embed/2IXJHMq9pS8?feature=oembed&autoplay=1&mute=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen=''></iframe></div>";

o_video_card_list.o_video_card_list_items[4].o_video_card_link_showcase_trigger_data_attr = "<div class='embed-youtube'><iframe title='Redemption' width='500' height='281' src='https://www.youtube.com/embed/18BTOs7cAkA?feature=oembed&autoplay=1&mute=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen=''></iframe></div>";

o_video_card_list.o_video_card_list_items[5].o_video_card_link_showcase_trigger_data_attr = "<div class='embed-youtube'><iframe title='Redemption' width='500' height='281' src='https://www.youtube.com/embed/g78PWOjWoqQ?feature=oembed&autoplay=1&mute=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen=''></iframe></div>";

o_video_card_list.o_video_card_list_items[6].o_video_card_link_showcase_trigger_data_attr = "<div class='embed-youtube'><iframe title='Testing' width='500' height='281' src='https://www.youtube.com/embed/lex6KJM3eWs?feature=oembed&autoplay=1&mute=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen=''></iframe></div>";

o_video_card_list.o_video_card_list_items[7].o_video_card_link_showcase_trigger_data_attr = "<div class='embed-youtube'><iframe title='Testing' width='500' height='281' src='https://www.youtube.com/embed/fGEX1pEpZ4M?feature=oembed&autoplay=1&mute=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen=''></iframe></div>";

module.exports = {
	related_videos_classes: 'u-background-color-picked-bluewood u-max-width-320@tablet u-padding-l-1@tablet u-border-l-1@tablet u-border-color-pale-sky-2',
	related_videos_wrap_classes: 'lrv-u-overflow-auto lrv-u-width-100p lrv-u-border-b-1 u-border-color-pale-sky-2 u-max-height-450 u-max-height-600@desktop-xl lrv-u-margin-t-050@mobile-max',
	o_more_from_heading,
	o_video_card_list,
};
