const clonedeep = require( 'lodash.clonedeep' );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype.js' );
const c_lazy_image_primary = clonedeep( c_lazy_image_prototype );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const linked_gallery_last_item = clonedeep( c_lazy_image );
linked_gallery_last_item.c_lazy_image_classes += ' a-hidden@mobile-max';

module.exports = {
	linked_gallery_url: '#',
	linked_gallery_title_text: 'post title',
	c_lazy_image_primary: c_lazy_image_primary,
	linked_gallery_items: [
		c_lazy_image,
		c_lazy_image,
		c_lazy_image,
		c_lazy_image
	],
	linked_gallery_last_item: linked_gallery_last_item
};
