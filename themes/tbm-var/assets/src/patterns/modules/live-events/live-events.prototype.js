const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.prototype' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' );
const c_span = clonedeep( c_span_prototype );
const c_span_secondary = clonedeep( c_span_prototype );

const c_dek_prototype = require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype' );
const c_dek = clonedeep( c_dek_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.blue' );
const o_more_link_desktop = clonedeep( o_more_link_prototype );
const o_more_link_mobile = clonedeep( o_more_link_prototype );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const c_tagline_prototype = require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' );
const c_tagline = clonedeep( c_tagline_prototype );

const vip_banner_prototype = require( '../vip-banner/vip-banner.300x250' );
const vip_banner = clonedeep( vip_banner_prototype );

const cxense_subscribe_widget = clonedeep( require( '../cxense-widget/cxense-widget.prototype' ) );

o_more_from_heading.c_heading.c_heading_text = 'Live Events';
o_more_from_heading.c_heading.c_heading_classes = 'lrv-u-color-black lrv-u-font-family-secondary u-font-size-25 lrv-u-font-weight-bold u-letter-spacing-001@mobile-max u-font-size-32@tablet';

c_span.c_span_text = 'Variety Music for Screens Summit';
c_span.c_span_classes = 'lrv-u-display-block lrv-u-font-family-secondary lrv-u-font-size-18 lrv-u-font-weight-bold lrv-u-text-align-center@mobile-max lrv-u-border-t-1 u-border-color-brand-secondary-40 lrv-u-padding-t-050 u-font-size-21@tablet u-font-size-28@desktop-xl';

c_dek.c_dek_text = 'All-day summit hosting the creators and greenlighters behind the entertainment offerings at the intersection of music and visual media.';
c_dek.c_dek_classes = 'lrv-u-color-black lrv-u-font-family-secondary u-font-size-13 lrv-u-text-align-center@mobile-max u-margin-t-025 lrv-u-margin-b-050 u-border-b-1@mobile-max u-border-color-brand-secondary-40 lrv-u-padding-b-050 u-font-size-15@tablet u-font-size-18@desktop-xl u-line-height-120';

o_more_link_desktop.o_more_link_classes += ' a-hidden@mobile-max';
o_more_link_desktop.c_link.c_link_text = 'Register Now';
o_more_link_desktop.c_link.c_link_classes = o_more_link_desktop.c_link.c_link_classes.replace( 'u-color-pale-sky-2', 'lrv-u-color-black' );

c_span_secondary.c_span_text = 'Confirmed Speakers';
c_span_secondary.c_span_classes = 'lrv-u-color-black lrv-u-font-family-secondary lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-text-transform-uppercase';

c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/100x100';
c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1';
c_lazy_image.c_lazy_image_classes = 'lrv-u-width-100p';

c_tagline.c_tagline_markup = '<strong>Cameron Crowe:</strong> Oscar-winning Filmmaker';
c_tagline.c_tagline_classes = 'lrv-u-color-black lrv-u-font-family-secondary lrv-u-font-size-12';
c_tagline.c_tagline_text = null;

o_more_link_mobile.c_link.c_link_text = 'Sign Up';
o_more_link_mobile.c_link.c_link_classes = o_more_link_mobile.c_link.c_link_classes.replace( 'u-color-pale-sky-2', 'lrv-u-color-black' );
o_more_link_mobile.o_more_link_classes += ' u-margin-t-075 u-padding-b-125 a-hidden@tablet';

cxense_subscribe_widget.cxense_id_attr = 'cx-module-events-300x250';

const live_events_images = [
	c_lazy_image,
	c_lazy_image,
	c_lazy_image,
];

const live_events_taglines = [
	c_tagline,
	c_tagline,
	c_tagline,
];

module.exports = {
	live_events_wrapper_classes: 'lrv-a-wrapper lrv-u-margin-t-1 lrv-a-grid a-cols3@tablet a-cols4@desktop-xl',
	live_events_classes: 'u-border-t-6@mobile-max u-border-color-picked-bluewood u-box-shadow-menu lrv-u-padding-lr-1 u-background-color-accent-c-40@tablet lrv-u-flex lrv-u-flex-direction-column@mobile-max u-padding-b-175@tablet a-span2@tablet a-span3@desktop-xl lrv-u-height-100p u-background-color-white@mobile-max',
	live_events_secondary_classes: 'u-min-width-250@tablet u-max-width-330@desktop-xl lrv-u-width-100p u-margin-t-2@tablet u-margin-l-2@tablet',
	live_events_images_classes: 'lrv-a-grid a-cols3 lrv-u-margin-b-050 u-margin-t-025@desktop-xl u-grid-gap-075@desktop-xl',
	o_more_from_heading,
	c_span,
	c_dek,
	o_more_link_desktop,
	o_more_link_mobile,
	c_span_secondary,
	live_events_images,
	live_events_taglines,
	cxense_subscribe_widget,
};
