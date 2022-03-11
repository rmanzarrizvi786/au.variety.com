const clonedeep = require( 'lodash.clonedeep' );

const homepage_voices_prototype = require( './homepage-voices.special' );
const homepage_voices = clonedeep( homepage_voices_prototype );

const o_tease_primary_prototype = require( '../../objects/o-tease/o-tease.special.secondary' );
const o_tease_primary = clonedeep( o_tease_primary_prototype );

const o_tease_secondary_prototype = require( '../../objects/o-tease/o-tease.special.tertiary' );
const o_tease_secondary = clonedeep( o_tease_secondary_prototype );

const c_footer_tagline_prototype = require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' );
const c_footer_tagline = clonedeep( c_footer_tagline_prototype );

const cxense_widget_prototype = require( '../cxense-widget/cxense-widget.prototype' );
const cxense_magazine_subscribe_widget = clonedeep( cxense_widget_prototype );

homepage_voices.homepage_voices_classes = homepage_voices.homepage_voices_classes.replace( 'u-background-color-picked-bluewood', 'lrv-u-background-color-white' );
homepage_voices.homepage_voices_classes = homepage_voices.homepage_voices_classes.replace( 'u-border-color-pale-sky-2', 'u-border-color-picked-bluewood' );

homepage_voices.homepage_voices_wrapper_classes = homepage_voices.homepage_voices_wrapper_classes.replace( 'lrv-u-padding-a-00', '' );

homepage_voices.c_span_title = null;

homepage_voices.homepage_voices_header_classes = 'lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-u-align-items-center u-border-b-1@tablet u-border-color-brand-secondary-40 lrv-u-margin-b-1 lrv-a-glue-parent';

homepage_voices.header_voices_footer_classes = 'lrv-a-glue-parent';

homepage_voices.c_lazy_image_mobile = null;

const {
	o_more_from_heading,
	c_span_subtitle,
	o_tease_list,
	o_more_link,
} = homepage_voices;

o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'u-padding-b-025', 'lrv-u-padding-b-050' );
o_more_from_heading.o_more_from_heading_classes += ' lrv-u-margin-t-050 lrv-u-width-100p@mobile-max u-border-color-brand-secondary-40 u-padding-b-075@tablet u-margin-b-00@tablet';
o_more_from_heading.c_heading.c_heading_classes = o_more_from_heading.c_heading.c_heading_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
o_more_from_heading.c_heading.c_heading_text = 'The Magazine';

c_span_subtitle.c_span_text = 'Robert Di Nero and Al Pacino Reunite in Netflix\'s Big Bet, \'The Irishman\''
c_span_subtitle.c_span_classes = c_span_subtitle.c_span_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
c_span_subtitle.c_span_classes = c_span_subtitle.c_span_classes.replace( 'u-margin-b-075', 'lrv-u-margin-b-050' );
c_span_subtitle.c_span_classes += ' u-line-height-120';

o_tease_primary.o_tease_classes = o_tease_primary.o_tease_classes.replace( 'lrv-u-padding-b-1', '' );
o_tease_primary.o_tease_classes += ' u-padding-lr-175@desktop-xl';
o_tease_primary.o_tease_primary_classes += ' a-hidden@mobile-max';
o_tease_primary.c_lazy_image.c_lazy_image_classes = o_tease_primary.c_lazy_image.c_lazy_image_classes.replace( 'a-hidden@mobile-max', '' );
o_tease_primary.c_lazy_image.c_lazy_image_crop_class = 'a-crop-13x17 a-hover-overlay a-hover-overlay--accent-c';
o_tease_primary.c_title.c_title_link_classes = o_tease_primary.c_title.c_title_link_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
o_tease_primary.c_title.c_title_link_classes = o_tease_primary.c_title.c_title_link_classes.replace( 'u-color-brand-accent-20:hover', 'u-color-brand-accent-80:hover' );

o_tease_secondary.o_tease_classes += ' u-padding-lr-175@desktop-xl';
o_tease_secondary.o_tease_classes += ' a-hidden@mobile-max';
o_tease_secondary.c_lazy_image.c_lazy_image_crop_class = 'a-crop-13x17 a-hover-overlay a-hover-overlay--accent-c';
o_tease_secondary.c_title.c_title_link_classes = o_tease_secondary.c_title.c_title_link_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
o_tease_secondary.c_title.c_title_link_classes = o_tease_secondary.c_title.c_title_link_classes.replace( 'u-color-brand-accent-20:hover', 'u-color-brand-accent-80:hover' );

o_more_link.o_more_link_classes += ' u-border-t-1@tablet u-border-color-brand-secondary-40';
o_more_link.c_link.c_link_text = 'More Cover Stories';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'lrv-u-color-white', 'u-color-pale-sky-2' );

c_footer_tagline.c_tagline_classes = 'magazine-login-link a-hidden@mobile-max u-font-family-body lrv-u-font-size-16 lrv-a-glue a-glue--a-50p u-transform-translate-a-n50p';
c_footer_tagline.c_tagline_markup = 'Already a subscriber? <a href="#subscribe">Access your digital edition</a>';
c_footer_tagline.c_tagline_text = null;

o_tease_list.o_tease_list_classes = o_tease_list.o_tease_list_classes.replace( 'u-border-b-1@tablet', '' );
o_tease_list.o_tease_list_classes += ' u-margin-lr-n175@desktop-xl';
o_tease_list.o_tease_list_item_classes = o_tease_list.o_tease_list_item_classes.replace( 'u-border-b-1@mobile-max', '' );
o_tease_list.o_tease_list_item_classes = o_tease_list.o_tease_list_item_classes.replace( 'u-border-color-pale-sky-2', 'u-border-color-brand-secondary-40' );

o_tease_list.o_tease_list_items = [
	o_tease_primary,
	o_tease_secondary,
	o_tease_secondary,
	o_tease_secondary,
];

homepage_voices.homepage_voices_wrapper_classes = homepage_voices.homepage_voices_wrapper_classes.replace( 'a-wrapper-padding-unset@mobile-max', '' );

// Account for collapsing margin from module above
homepage_voices.homepage_voices_wrapper_classes += ' u-margin-t-125 u-padding-t-125 u-margin-t-00@tablet';

cxense_magazine_subscribe_widget.cxense_id_attr = 'cx-module-magazine';

module.exports = {
	...homepage_voices,
	cxense_magazine_subscribe_widget,
	c_footer_tagline,
};
