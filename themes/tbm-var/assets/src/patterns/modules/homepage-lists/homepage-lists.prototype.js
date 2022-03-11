const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.homepage' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );
const o_more_from_heading_desktop = clonedeep( o_more_from_heading_prototype );const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const c_title_prototype = require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' );
const c_title = clonedeep( c_title_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.blue' );
const o_more_link = clonedeep( o_more_link_prototype );

const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' );
const c_icon = clonedeep( c_icon_prototype );

o_more_from_heading.o_more_from_heading_classes += ' a-hidden@tablet';
o_more_from_heading.c_heading.c_heading_text = 'Lists';
o_more_from_heading.c_v_icon = c_icon;

c_icon.c_icon_name = 'list';
c_icon.c_icon_classes = 'lrv-u-margin-l-050 u-margin-l-1@tablet u-width-25 u-height-25';

o_more_from_heading_desktop.o_more_from_heading_classes = o_more_from_heading_desktop.o_more_from_heading_classes.replace( 'u-margin-b-125@tablet', 'u-margin-b-025@tablet' );
o_more_from_heading_desktop.o_more_from_heading_classes += ' a-hidden@mobile-max u-padding-t-00@tablet u-margin-l-025@tablet';
o_more_from_heading_desktop.c_heading.c_heading_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-32';
o_more_from_heading_desktop.c_heading.c_heading_text = 'Lists';
o_more_from_heading_desktop.c_v_icon = c_icon;

c_lazy_image.c_lazy_image_crop_class = 'a-crop-73x49 a-crop-16x9@desktop-xl';
c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/351x234';
c_lazy_image.c_lazy_image_classes = 'u-margin-lr-n050@mobile-max u-width-100p@tablet lrv-u-margin-r-1@tablet';
c_lazy_image.c_lazy_image_link_url = '#list';

c_title.c_title_text = 'The Best Horror Films to Stream Right Now';
c_title.c_title_classes = 'lrv-u-font-family-primary lrv-u-font-weight-normal lrv-u-margin-t-050 u-font-size-25 u-font-size-28@tablet u-font-size-36@desktop-xl u-letter-spacing-003 u-line-height-110 u-margin-l-025@tablet u-max-width-70p@desktop-xl';
c_title.c_title_link_classes = 'lrv-u-color-black u-color-brand-accent-80:hover';

o_more_link.c_link.c_link_text = 'More Lists';
o_more_link.o_more_link_classes += ' u-border-t-1@mobile-max lrv-u-margin-t-1 lrv-u-padding-t-050 lrv-u-text-align-right u-border-color-brand-secondary-40 u-margin-t-auto@tablet';

module.exports = {
	lists_classes: 'lrv-u-flex lrv-u-flex-direction-column@mobile-max u-border-color-picked-bluewood lrv-u-padding-lr-050 u-padding-lr-1@tablet u-padding-b-075 u-padding-tb-125@tablet u-border-t-6 u-box-shadow-menu lrv-u-height-100p lrv-u-background-color-white',
	lists_inner_classes: 'lrv-u-flex lrv-u-flex-direction-column u-max-width-30p@tablet u-max-width-45p@desktop-xl u-width-100p@desktop-xl',
	o_more_from_heading,
	c_lazy_image,
	o_more_from_heading_desktop,
	c_title,
	o_more_link,
};
