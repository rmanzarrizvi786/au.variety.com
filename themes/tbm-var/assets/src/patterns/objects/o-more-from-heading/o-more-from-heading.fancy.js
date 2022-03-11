const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( './o-more-from-heading.prototype' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

o_more_from_heading.c_heading.c_heading_text = 'The Emmys';
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'u-padding-t-075', 'lrv-u-padding-t-050' );
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'lrv-u-margin-b-050', 'lrv-u-padding-t-050' );
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'lrv-u-margin-b-050', '' );
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'u-margin-b-125@tablet', '' );
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'u-justify-content-center@mobile-max', 'lrv-u-justify-content-center' );
o_more_from_heading.c_heading.c_heading_classes = 'lrv-u-font-family-primary u-font-size-26 lrv-u-font-weight-normal u-letter-spacing-001 lrv-u-color-white u-font-size-32@tablet';

module.exports = {
	...o_more_from_heading,
};
