const clonedeep = require( 'lodash.clonedeep' );

const c_play_icon_prototype = require( '../../components/c-play-badge/c-play-badge.prototype.js' );
const c_play_icon = clonedeep( c_play_icon_prototype );

const o_indicator_prototype = require( '../o-indicator/o-indicator.prototype.js' );
const o_indicator = clonedeep( o_indicator_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' );
const c_span = clonedeep( c_span_prototype );

const c_dek_prototype = require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype.js' );
const c_dek = clonedeep( c_dek_prototype );

c_heading.c_heading_text = 'Video title goes here';
c_heading.c_heading_link_classes = 'lrv-u-color-white lrv-u-display-block js-VideoShowcasePlayerHeading';
c_heading.c_heading_classes = 'lrv-u-color-white lrv-u-font-weight-normal lrv-u-font-family-primary lrv-u-font-size-24 u-font-size-32@tablet u-font-size-36@desktop-xl u-line-height-120 u-line-height-small@tablet u-order-n1@mobile-max lrv-u-font-weight-bold lrv-u-cursor-pointer a-truncate-ellipsis';
c_heading.c_heading_url = '#single-url';
c_heading.c_heading_text = 'Joaquin Phoenix Thanks Late Brother River in Emotional Speech at TIFF';

c_play_icon.c_play_badge_classes = 'lrv-a-glue a-glue--l-13 a-glue--b-25 a-glue--b-30@tablet a-glue--l-28@tablet u-width-40 u-height-40 u-width-60@tablet u-height-60@tablet is-to-be-hidden';

o_indicator.c_span.c_span_text = 'Popular on Variety';
o_indicator.o_indicator_classes = 'lrv-u-margin-b-025 u-margin-t-050@tablet';
o_indicator.c_span.c_span_classes = 'lrv-u-color-white lrv-u-text-transform-uppercase a-hidden@mobile-max u-font-family-basic u-font-size-13 u-letter-spacing-009';

c_span.c_span_text = '2:30';
c_span.c_span_classes = 'u-font-family-basic lrv-u-font-size-14 u-color-brand-secondary-60 js-VideoShowcasePlayerTime lrv-u-padding-t-050';

c_dek.c_dek_classes = 'lrv-u-display-none js-VideoShowcasePlayerDek';

module.exports = {
	o_video_card_permalink_url: "#single_url",
	o_video_card_alt_attr: "Thumbnail image",
	o_video_card_image_url: "https://source.unsplash.com/random/595x333",
	o_video_card_lazy_image_url: 'https://source.unsplash.com/random/595x333',
	o_video_card_caption_text: "Here is some caption text",
	o_video_card_link_showcase_trigger_data_attr: "<div class='embed-youtube'><iframe title='Spring - Blender Open Movie' width='500' height='281' src='https://www.youtube.com/embed/WhWc3b3KhnY?feature=oembed&autoplay=1&mute=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen=''></iframe></div>",
	o_video_card_link_showcase_type_data_attr: "oembed",
	o_video_card_link_showcase_dek_data_attr: "Dek test",
	o_video_card_link_showcase_title_data_attr: "Title test",
	o_video_card_link_showcase_permalink_data_url: "#url_test",
	o_video_card_link_showcase_time_data_attr: "10:00",
	modifier_class: "",
	o_video_card_classes: 'u-background-color-picked-bluewood u-margin-r-1@tablet lrv-u-justify-content-space-between',
	o_video_card_crop_class: "lrv-a-crop-16x9 lrv-a-glue-parent c-play-badge-parent",
	o_video_card_image_classes: "is-to-be-hidden lrv-u-display-block",
	o_video_card_is_player: true,
	c_label: "",
	o_video_card_crop_data_attr: "",
	o_video_card_meta_classes: 'lrv-u-flex lrv-u-flex-direction-column lrv-u-width-100p u-padding-lr-075@mobile-max lrv-u-padding-tb-050 u-position-relative',
	o_video_card_link_classes: 'lrv-u-width-100p',
	o_video_card_permalink_classes: 'u-margin-r-050@mobile-max',
	o_indicator,
	c_play_icon,
	c_heading,
	c_span,
	c_dek,
};
