const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' );
const c_heading = clonedeep( c_heading_prototype );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' );
const c_link = clonedeep( c_link_prototype );

c_heading.c_heading_text = 'Related Articles';
c_heading.c_heading_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-26@tablet a-pull-up-above-item';

c_lazy_image.c_lazy_image_classes = 'a-pull-up-item a-hidden@mobile-max u-box-shadow-medium lrv-u-margin-b-050';
c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-2x3';
c_lazy_image.c_lazy_image_placeholder_url = 'https://farm5.staticflickr.com/4078/5441060528_31db7838ba_z.jpg';
c_lazy_image.c_lazy_image_screen_reader_text = false;

c_link.c_link_text = 'The Best Shows to See in Dusseldorf';

module.exports = {
	article_related_links_classes: '',
	article_related_item_classes: 'u-border-color-brand-primary',
	c_heading: c_heading,
	c_lazy_image: c_lazy_image,
	article_related_links: [
		c_link,
		c_link
	]
};
