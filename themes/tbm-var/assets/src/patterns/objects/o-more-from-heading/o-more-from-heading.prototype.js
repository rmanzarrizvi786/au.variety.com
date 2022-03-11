const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

c_heading.c_heading_classes = 'lrv-u-border-b-1 lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-16 lrv-u-font-weight-bold lrv-u-padding-b-050 lrv-u-text-align-center@mobile-max u-text-transform-uppercase@mobile-max u-border-color-pale-sky-2 u-letter-spacing-002@mobile-max lrv-u-margin-b-1 u-font-size-32@tablet u-padding-b-1@tablet u-margin-b-125@tablet';
c_heading.c_heading_text = 'More From';

module.exports = {
	o_more_from_heading_classes: 'lrv-u-flex u-justify-content-center@mobile-max lrv-u-align-items-center lrv-u-padding-tb-050',
	c_heading,
};
