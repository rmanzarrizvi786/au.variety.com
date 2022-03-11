const clonedeep = require( 'lodash.clonedeep' );

const o_more_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.variety-vip.js' );
const o_more_heading = clonedeep( o_more_heading_prototype );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype.js' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const c_title_prototype = require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype.js' );
const c_title = clonedeep( c_title_prototype );

const c_dek_prototype = require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype.js' );
const c_dek = clonedeep( c_dek_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.prototype.js' );
const o_more_link = clonedeep( o_more_link_prototype );

o_more_heading.c_v_icon = null;
o_more_heading.c_heading.c_heading_text = 'Upcoming Events';
o_more_heading.o_more_from_heading_classes = o_more_heading.o_more_from_heading_classes.replace( 'lrv-u-text-align-center', 'lrv-u-text-align-center@mobile-max' );
o_more_heading.c_heading.c_heading_classes = o_more_heading.c_heading.c_heading_classes.replace( 'u-letter-spacing-021', 'u-letter-spacing-040@mobile-max' );
o_more_heading.c_heading.c_heading_classes += ' u-font-family-secondary@tablet u-font-size-32@tablet';

// TODO: Remove the overriding of the placeholder URL as we load the lazy loading script.
c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/300x300';
c_lazy_image.c_lazy_image_crop_class = 'a-crop-1x1';
c_lazy_image.c_lazy_image_classes = 'u-margin-t-050';
c_lazy_image.c_lazy_image_link_url = '#event_url';
c_lazy_image.c_lazy_image_src_url = 'https://source.unsplash.com/random/300x300';
c_lazy_image.c_lazy_image_srcset_attr = 'https://source.unsplash.com/random/300x300,https://source.unsplash.com/random/450x450 1.5x,https://source.unsplash.com/random/600x600 2x';
c_lazy_image.c_lazy_image_height_attr = '300';
c_lazy_image.c_lazy_image_width_attr = '300';

c_title.c_title_text = 'Silicon Valleywood presented by PwC';
c_title.c_title_classes = 'lrv-u-font-family-secondary lrv-u-text-align-center lrv-u-text-transform-uppercase u-font-size-18 u-letter-spacing-030 u-line-height-120';
c_title.c_title_link_classes = 'lrv-u-color-black';
c_title.c_title_url = '#event_url';

c_dek.c_dek_text = 'The invite-only event brings together the most influential decision-makers in entertainment and tech to network, connect and explore future opportunities that bridge the gap';
c_dek.c_dek_classes = 'lrv-u-text-align-center u-font-family-16 u-margin-b-150@tablet';

o_more_link.c_link.c_link_text = 'Learn More';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'a-icon-long-right-arrow', 'a-icon-long-right-arrow-dark' );
o_more_link.c_link.c_link_url = '#event_url';

module.exports = {
	upcoming_events_classes: 'u-background-image-slash@mobile-max u-border-t-6@mobile-max u-border-color-brand-secondary-50 u-padding-lr-175@mobile-max u-max-width-300@tablet',
	upcoming_events_inner_classes: 'lrv-u-padding-t-1 lrv-u-padding-lr-2 lrv-u-text-align-center u-background-image-slash@tablet u-padding-b-150',
	o_more_heading,
	c_lazy_image,
	c_title,
	c_dek,
	o_more_link,
};
