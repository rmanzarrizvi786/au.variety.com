const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

c_heading.c_heading_classes = 'u-text-transform-uppercase@mobile-max u-font-family-primary@mobile-max lrv-u-font-family-secondary lrv-u-font-weight-normal lrv-u-font-size-32 lrv-u-text-align-center@mobile-max u-font-weight-bold@tablet u-letter-spacing-040@mobile-max';
c_heading.c_heading_text = 'More Special Reports';

module.exports = {
	special_report_landing_heading_classes: 'lrv-a-wrapper lrv-u-margin-b-1 u-margin-t-025 u-margin-b-2@tablet u-margin-t-250@tablet',
	c_heading,
};
