const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' );
const c_tagline_prototype = require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' );
const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' );
const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' );

const c_icon = clonedeep( c_icon_prototype );
c_icon.c_icon_classes = 'u-width-16 u-height-16 u-color-pale-sky';
c_icon.c_icon_name = 'down-caret';

const author_details_prototype = require( '../author-details/author-details.prototype' );
const author_details = clonedeep( author_details_prototype );

c_link_author = clonedeep( c_link_prototype );
c_link_author.c_link_text = 'Kevin Tran';
c_link_author.c_link_url = '#';

const c_tagline = clonedeep( c_tagline_prototype );
c_tagline.c_tagline_classes = 'lrv-u-display-inline';
c_tagline.c_tagline_markup = '<a href="#">Link 1</a>, <a href="#">Link 2</a>';
c_tagline.c_tagline_text = false;

const c_lazy_image = clonedeep( c_lazy_image_prototype );
c_lazy_image.c_lazy_image_classes = 'lrv-u-width-100p u-max-width-60 lrv-u-flex-shrink-0 a-hidden@mobile-max lrv-u-margin-r-1';
c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1';
c_lazy_image.c_lazy_image_alt_attr = 'Author profile picture';
c_lazy_image.c_lazy_image_img_classes += ' lrv-u-border-radius-50p';

module.exports = {
	author_classes: 'u-font-size-13 u-font-size-16@tablet lrv-u-font-family-secondary',
	author_inner_classes: 'lrv-u-flex',
	author_byline_classes: 'u-letter-spacing-003 lrv-u-margin-r-050 u-color-pale-sky lrv-u-font-weight-bold a-inner-links--highlighter-hover a-inner-links--currentColor',
	author_wrapper_classes: 'u-flex@tablet lrv-u-width-100p u-max-width-600@tablet u-max-width-640@desktop-xl',
	author_content_classes: 'lrv-u-flex lrv-u-flex-direction-column lrv-u-width-100p u-margin-t-025@tablet',
	author_toggle_classes: '',
	author_timestamp_outer_classes: 'u-margin-l-1@tablet',
	is_byline_only: false,
	c_tagline: c_tagline,
	c_lazy_image: c_lazy_image,
	c_link: c_link_author,
	c_icon: c_icon,
	author_details: author_details,
	c_tagline_optional: false,
};
