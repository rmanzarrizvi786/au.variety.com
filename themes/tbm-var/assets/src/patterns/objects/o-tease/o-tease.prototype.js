const clonedeep = require( 'lodash.clonedeep' );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype.js' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const c_title_prototype = require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' );
const c_title = clonedeep( c_title_prototype );

const c_timestamp_prototype = require( '@penskemediacorp/larva-patterns/components/c-timestamp/c-timestamp.prototype.js' );
const c_timestamp = clonedeep( c_timestamp_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link = clonedeep( c_link_prototype );

c_lazy_image.c_lazy_image_classes = '';
c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-16x9';
c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/327x184';
c_lazy_image.c_lazy_image_link_url = '#';
c_title.c_title_classes = 'lrv-u-font-family-secondary u-font-size-15 u-font-size-16@tablet u-line-height-120 u-margin-r-050@mobile-max';
c_title.c_title_link_classes = 'lrv-u-color-black lrv-u-display-block u-color-brand-secondary-50:hover';
c_title.c_title_text = 'HBO Max Non-Fiction Exec Team Takes Shape With Lizzie Fox';
c_link.c_link_url = '#';
c_link.c_link_text = 'Film';
c_link.c_link_classes = 'lrv-u-display-block lrv-u-text-transform-uppercase a-hidden@mobile-max u-color-brand-secondary-50 u-font-family-accent u-font-size-13 u-letter-spacing-009 u-margin-t-075';
c_timestamp.c_timestamp_text = '2 hours Ago';
c_timestamp.c_timestamp_classes = 'a-hidden@mobile-max u-color-brand-secondary-50 u-font-family-accent u-font-size-13 u-letter-spacing-005';

module.exports = {
	o_tease_url: '',
	o_tease_classes: 'lrv-u-flex lrv-u-align-items-center lrv-u-padding-b-1 u-padding-t-075',
	o_tease_link_classes: 'lrv-u-display-contents',
	o_tease_primary_classes: 'lrv-u-flex-grow-1',
	o_tease_secondary_classes: 'lrv-u-flex-shrink-0 u-margin-r-125@tablet u-order-n1@tablet u-width-44p@mobile-max u-width-177@tablet',
	o_span_group: null,
	c_dek: null,
	c_title,
	c_lazy_image,
	c_timestamp,
	c_link,
};
