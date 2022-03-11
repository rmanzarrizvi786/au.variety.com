const clonedeep = require( 'lodash.clonedeep' );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype.js' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

c_lazy_image.c_lazy_image_link_classes += ' a-content-ignore';
c_lazy_image.c_lazy_image_classes = 'u-width-192@mobile-max lrv-u-width-100p u-border-t-6@tablet u-border-color-brand-secondary-50 u-margin-r-075@mobile-max';
c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/174x100';
c_lazy_image.c_lazy_image_link_url = '#';
c_lazy_image.c_lazy_image_srcset_attr = 'https://source.unsplash.com/random/120x69 240w,https://source.unsplash.com/random/120x69 320w,https://source.unsplash.com/random/120x69 500w,https://source.unsplash.com/random/174x100 640w,https://source.unsplash.com/random/174x100 1024w';

c_heading.c_heading_url = '#';
c_heading.c_heading_text = "'Grimm' Gets Resurrected for Streaming";
c_heading.c_heading_classes += ' a-content-ignore';
c_heading.c_heading_link_classes = 'lrv-u-color-black u-color-brand-secondary-50:hover lrv-u-display-block lrv-u-padding-t-1 u-padding-b-075 a-content-ignore a-font-secondary-bold-s';

module.exports = {
	related_article_classes: 'lrv-u-flex lrv-u-flex-direction-column@tablet lrv-u-align-items-center lrv-u-border-b-1 lrv-u-padding-tb-1@mobile-max u-border-color-brand-secondary-50 u-align-items-flex-start',
	c_lazy_image,
	c_heading
};
