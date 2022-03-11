const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( './o-more-from-heading.prototype' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const c_heading = clonedeep( require( '../../components/c-heading/c-heading.accent-m' ) );

o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'lrv-u-padding-tb-050', '' );
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'lrv-u-padding-t-050', '' );
o_more_from_heading.o_more_from_heading_classes = o_more_from_heading.o_more_from_heading_classes.replace( 'u-padding-tb-1@tablet', '' );

o_more_from_heading.c_heading = c_heading;
o_more_from_heading.c_heading.c_heading_text = 'Politics';
o_more_from_heading.c_v_icon = null;

module.exports = {
	...o_more_from_heading,
};
