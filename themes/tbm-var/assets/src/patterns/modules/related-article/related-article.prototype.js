const clonedeep = require( 'lodash.clonedeep' );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype.js' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

c_lazy_image.c_lazy_image_classes = 'u-min-width-110 lrv-u-margin-r-1';
c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/125x70';
c_lazy_image.c_lazy_image_link_url = '#single_url';
c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-4x3';
c_lazy_image.c_lazy_image_srcset_attr = 'https://source.unsplash.com/random/125x70 125w,https://source.unsplash.com/random/250x140 250w';
c_lazy_image.c_lazy_image_link_classes = 'lrv-u-display-block a-hover-overlay a-hover-overlay--accent-c a-content-ignore';

c_heading.c_heading_url = '#single_url';
c_heading.c_heading_text = "Channing Tatum’s Free Association and Temple Hill Team on ‘Soundtrack of Silence’ (EXCLUSIVE)";
c_heading.c_heading_classes = 'lrv-u-font-weight-normal a-content-ignore';
c_heading.c_heading_link_classes = 'lrv-u-color-black u-color-brand-secondary-50:hover lrv-u-display-block lrv-u-font-size-14 u-line-height-120 u-max-height-85 lrv-u-font-family-secondary a-truncate-ellipsis a-content-ignore';

module.exports = {
	related_article_classes: 'lrv-u-flex lrv-u-align-items-top u-min-width-290 u-width-48p lrv-u-margin-r-050',
	c_lazy_image,
	c_heading
};
