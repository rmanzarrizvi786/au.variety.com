const clonedeep = require( 'lodash.clonedeep' );

const c_span = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
const c_title = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' ) );
const c_dek = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' ) );
const c_link = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' ) );
const c_timestamp = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-timestamp/c-timestamp.prototype' ) );
const c_lazy_image = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' ) );
const c_play_badge = clonedeep( require( '../../components/c-play-badge/c-play-badge.prototype' ) );

c_span.c_span_text = 'Global';
c_span.c_span_classes = 'a-font-basic-s lrv-u-padding-b-050 lrv-u-text-transform-uppercase u-color-pale-sky-2 ';
c_span.c_span_link_classes += ' lrv-a-unstyle-link';
c_span.c_span_url = '#tax';

c_title.c_title_text = 'CBS Evening News Readies for The Streaming Wars';
c_title.c_title_classes = 'a-font-secondary-bold-m';
c_title.c_title_link_classes += ' lrv-u-color-black u-color-brand-accent-80:hover lrv-u-display-block';

c_dek.c_dek_classes = 'lrv-u-color-black a-font-secondary-m u-margin-t-025 lrv-u-margin-b-050 a-hidden@mobile-max';
c_dek.c_dek_text = 'Norah O’Donnell and Susan Zirinsky Are Out To Remake CBS’ Flagship Newscast at a Time When Journalism is Under Seige';

c_link.c_link_text = 'By Cynthia Littleton';
c_link.c_link_classes = 'a-font-secondary-bold-4xs u-color-pale-sky-2 u-color-brand-accent-80:hover';

c_timestamp.c_timestamp_text = '2 hours';
c_timestamp.c_timestamp_classes = 'a-font-basic-xs u-color-iron-grey lrv-u-flex-shrink-0';

c_lazy_image.c_lazy_image_classes += ' lrv-u-height-100p lrv-u-overflow-hidden';
c_lazy_image.c_lazy_image_link_url = '#post';
c_lazy_image.c_lazy_image_crop_class = 'lrv-u-height-100p lrv-a-crop-1x1 a-crop-3x2@desktop-xl';
c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/630x420';

c_play_badge.c_play_badge_classes = 'u-width-40 u-height-40 lrv-u-margin-a-1 lrv-a-glue@tablet';

module.exports = {
	o_story_classes: 'u-border-color-brand-secondary-40@mobile-max lrv-u-background-color-white u-padding-a-075',
	o_story_primary_classes: 'u-margin-l-075@tablet',
	o_story_secondary_classes: 'u-max-width-128@mobile-max u-order-n1@tablet u-margin-l-1@mobile-max lrv-u-width-100p',
	o_story_meta_classes: 'a-hidden@mobile-max lrv-u-margin-t-auto',
	video_permalink_url: false,
	c_span_secondary: false,
	c_span,
	c_title,
	c_dek,
	c_link,
	c_timestamp,
	c_lazy_image,
	c_lazy_image_play_badge_classes: 'u-flex@mobile-max lrv-u-justify-content-center lrv-u-align-items-center',
	c_play_badge,
};
