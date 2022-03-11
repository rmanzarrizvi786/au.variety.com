const clonedeep = require( 'lodash.clonedeep' );

const special_reports_carousel_prototype = require( './special-reports-carousel.prototype.js' );
const special_reports_carousel = clonedeep( special_reports_carousel_prototype );

special_reports_carousel.o_more_link.o_more_link_classes += ' lrv-u-margin-r-050';
special_reports_carousel.o_more_from_heading.c_heading.c_heading_classes += ' lrv-u-padding-lr-050';
special_reports_carousel.special_reports_carousel_classes = 'u-background-image-slash u-border-t-6 u-border-b-6 u-border-color-brand-secondary-50 u-padding-t-150@tablet u-padding-b-250@tablet u-margin-lr-n050';

module.exports = {
	...special_reports_carousel,
};
