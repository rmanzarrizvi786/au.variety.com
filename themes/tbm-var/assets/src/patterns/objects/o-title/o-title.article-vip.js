const clonedeep = require( 'lodash.clonedeep' );

const o_title_prototype = require( './o-title.prototype.js' );
const o_title = clonedeep( o_title_prototype );

o_title.c_heading.c_heading_classes += ' lrv-u-text-align-center lrv-u-text-transform-uppercase u-margin-t-075@tablet lrv-u-padding-tb-050 lrv-u-padding-lr-2 u-font-size-50 u-font-size-70@tablet u-letter-spacing-2';

module.exports = {
	...o_title
};
