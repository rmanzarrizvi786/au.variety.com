const clonedeep = require( 'lodash.clonedeep' );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype.js' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const o_indicator_prototype = require( '@penskemediacorp/larva-patterns/objects/o-indicator/o-indicator.prototype.js' );
const o_indicator = clonedeep( o_indicator_prototype );

const c_title_prototype = require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype.js' );
const c_title_desktop = clonedeep( c_title_prototype );
const c_title = clonedeep( c_title_prototype );

const c_timestamp_prototype = require( '@penskemediacorp/larva-patterns/components/c-timestamp/c-timestamp.prototype.js' );
const c_timestamp = clonedeep( c_timestamp_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/300x225';
c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-4x3';
c_lazy_image.c_lazy_image_classes = 'lrv-u-border-a-1 u-border-color-dusty-grey u-box-shadow-small-medium u-margin-b-075';
c_lazy_image.c_lazy_image_link_url = '#';

o_indicator.o_indicator_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold a-hidden@tablet u-font-size-15';
o_indicator.c_span.c_span_url = '#';
o_indicator.c_span.c_span_text = 'Sports Betting';
o_indicator.c_span.c_span_link_classes = 'lrv-u-color-brand-primary';
o_indicator.c_span.c_span_classes = ''; 

c_title.c_title_classes = 'lrv-u-font-family-secondary a-hidden@tablet u-font-size-15';
c_title.c_title_link_classes = 'lrv-u-color-black';
c_title.c_title_text = '2nd Edition - May 2019';

c_heading.c_heading_text = 'Sports Betting';
c_heading.c_heading_classes = 'lrv-u-color-black lrv-u-text-transform-uppercase lrv-u-font-size-24 lrv-u-line-height-small lrv-u-font-family-secondary';

c_timestamp.c_timestamp_text = 'May 2019';
c_timestamp.c_timestamp_classes = 'lrv-u-color-black u-font-size-21 lrv-u-font-family-secondary';

module.exports = {
	o_slide_link_url: '#slide_link',
	o_slide_classes: 'lrv-a-glue-parent lrv-u-margin-lr-auto lrv-u-text-align-center u-width-215 u-width-300@tablet a-scale-110@tablet:hover',
	o_slide_meta_classes: 'lrv-a-glue lrv-a-glue--b-0 lrv-a-glue--l-0 lrv-a-glue--r-0 lrv-a-glue--t-0 lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-center lrv-u-padding-lr-1 lrv-u-padding-tb-1 a-fade--in@tablet a-hover-overlay@tablet a-hover-overlay--white a-hover-overlay--position-absolute a-hover-overlay--before a-hidden@mobile-max',
	c_lazy_image,
	o_indicator,
	c_title,
	c_heading,
	c_timestamp,
};
