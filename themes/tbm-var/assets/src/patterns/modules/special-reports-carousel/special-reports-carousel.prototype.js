const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading = clonedeep( require( '../../objects/o-more-from-heading/o-more-from-heading.variety-vip.js' ) );

const o_slide_prototype = require( '../../objects/o-slide/o-slide.prototype.js' );
const o_slide = clonedeep( o_slide_prototype );

const o_more_link_prototype = require( '../../objects/o-more-link/o-more-link.prototype.js' );
const o_more_link = clonedeep( o_more_link_prototype );

o_more_from_heading.c_v_icon = null;
o_more_from_heading.c_heading.c_heading_text = 'Special Reports';
o_more_from_heading.c_heading.c_heading_classes = o_more_from_heading.c_heading.c_heading_classes.replace( 'u-letter-spacing-021', 'u-letter-spacing-040@mobile-max' );
o_more_from_heading.c_heading.c_heading_classes += ' u-font-family-secondary@tablet u-margin-b-175@tablet';

o_more_link.o_more_link_classes = 'lrv-u-text-align-right u-margin-t-150 lrv-u-margin-b-1';
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'a-icon-long-right-arrow', 'a-icon-long-right-arrow-dark' );
o_more_link.c_link.c_link_text = 'More Special Reports';

const special_report_items = [
	o_slide,
	o_slide,
	o_slide,
	o_slide,
	o_slide,
	o_slide,
];

module.exports = {
	special_reports_carousel_classes: 'u-background-image-slash u-border-t-6 u-border-b-6 u-border-color-brand-secondary-50 u-padding-b-250 u-padding-t-150@tablet u-padding-b-250@tablet',
	special_report_inner_classes: 'js-Flickity--nav-top-right js-Flickity--hide-nav@mobile-max js-Flickity--isWrapAround',
	special_report_item_classes: 'lrv-u-margin-lr-050 lrv-u-padding-tb-1@tablet u-margin-r-250@tablet',
	o_more_from_heading,
	special_report_items,
	o_more_link,
};
