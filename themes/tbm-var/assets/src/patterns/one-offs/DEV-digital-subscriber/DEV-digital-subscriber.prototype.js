const clonedeep = require( 'lodash.clonedeep' );

const c_tagline = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' ) );
const c_title = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' ) );
const c_heading = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );
const c_lazy_image = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' ) );

c_heading.c_heading_classes += ' lrv-u-margin-b-2 lrv-u-text-align-center';
c_heading.c_heading_text = 'Variety Premier subscribers have access to the digital edition of Variety, production charts, archives and other business tools.';

c_tagline.c_tagline_classes += ' u-font-family-body';

c_title.c_title_url = false;
c_title.c_title_classes = 'lrv-font-size-36 lrv-u-font-weight-bold lrv-u-text-align-center';

c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-3x4';

module.exports = {
	c_heading: c_heading,
	c_title: c_title,
	c_title_second: clonedeep( c_title ),
	c_title_third: clonedeep( c_title ),
	c_tagline: c_tagline,
	c_tagline_second: clonedeep( c_tagline ),
	c_lazy_image: c_lazy_image
};
