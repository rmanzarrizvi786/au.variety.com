const clonedeep = require( 'lodash.clonedeep' );

const c_lazy_image = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' ) );

const o_more_link = clonedeep( require( '../../objects/o-more-link/o-more-link.blue' ) );

o_more_link.c_link.c_link_classes += ' a-content-ignore';

c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-3x4';

module.exports = {
	c_lazy_image,
	o_more_link
};
