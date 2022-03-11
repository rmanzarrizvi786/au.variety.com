const clonedeep = require( 'lodash.clonedeep' );

const c_lazy_image = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' ) );
const c_title = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' ) );
const c_tagline = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' ) );

c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-3x2';
c_lazy_image.c_lazy_image_placeholder_url = '/assets/build/images/variety-over-the-years.jpg';
c_lazy_image.c_lazy_image_srcset_attr = false;

c_title.c_title_text = 'Welcome!';
c_title.c_title_url = false;

c_tagline.c_tagline_text = 'As a Variety Print Plus subscriber, you are entitled to access and search the past 15 years of Variety’s print publications, which are hosted online in searchable PDF format for your convenience.';

module.exports = {
	c_lazy_image,
	c_title,
	c_tagline
};
