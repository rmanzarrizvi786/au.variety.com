const clonedeep = require( 'lodash.clonedeep' );

const o_taxonomy_item_prototype = require( '../o-taxonomy-item/o-taxonomy-item.prototype' );
const o_taxonomy_item = clonedeep( o_taxonomy_item_prototype );

const c_timestamp_prototype = require( '@penskemediacorp/larva-patterns/components/c-timestamp/c-timestamp.prototype' );
const c_timestamp = clonedeep( c_timestamp_prototype );

const c_title_prototype = require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' );
const c_title = clonedeep( c_title_prototype );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const c_play_badge_prototype = require( '../../components/c-play-badge/c-play-badge.prototype' );
const c_play_badge = clonedeep( c_play_badge_prototype );

o_taxonomy_item.c_span.c_span_classes = o_taxonomy_item.c_span.c_span_classes.replace( 'lrv-u-margin-b-025', '' );
o_taxonomy_item.c_span.c_span_classes += ' u-border-color-iron-grey';
o_taxonomy_item.c_span.c_span_link_classes = o_taxonomy_item.c_span.c_span_link_classes.replace( 'lrv-u-padding-t-050', '' );
o_taxonomy_item.c_span.c_span_link_classes = o_taxonomy_item.c_span.c_span_link_classes.replace( 'lrv-u-padding-b-025', '' );
o_taxonomy_item.c_span.c_span_link_classes += ' u-line-height-1';

c_timestamp.c_timestamp_text = '24m ago';
c_timestamp.c_timestamp_classes = 'u-font-family-basic u-font-size-11 u-color-iron-grey';

c_title.c_title_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold u-font-size-13 u-font-size-18@tablet u-font-size-21@desktop-xl u-line-height-120';
c_title.c_title_link_classes = 'lrv-u-color-black u-color-brand-accent-80:hover';
c_title.c_title_text = 'The Beatles to Release Limited Edition ‘Singles Collection’ Vinyl Boxed Set';

c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/177x99';
c_lazy_image.c_lazy_image_link_url = '#post';

module.exports = {
	o_tease_classes: 'lrv-u-flex',
	o_tease_data_attributes: '',
	o_tease_url: '',
	o_tease_link_classes: '',
	o_tease_primary_classes: 'lrv-u-width-100p',
	o_tease_secondary_classes: 'u-width-85p u-order-n1@tablet lrv-u-margin-r-1@tablet u-max-width-175@tablet lrv-a-glue-parent u-margin-l-1@mobile-max',
	o_tease_meta_classes: 'lrv-u-flex lrv-u-align-items-center a-separator-r-1 a-separator-spacing--r-050 a-separator-spacing--r-075@tablet lrv-u-margin-b-025',
	c_play_badge_wrapper_classes: 'lrv-a-glue a-glue--a-50p lrv-u-display-block u-width-40 u-height-40 u-transform-translate-a-n50p',
	video_permalink_url: null,
	is_video: false,
	o_taxonomy_item,
	c_timestamp,
	c_title,
	c_lazy_image,
	c_play_badge,
};
