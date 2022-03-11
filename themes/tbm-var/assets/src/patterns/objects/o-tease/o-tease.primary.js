const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.prototype.js' );
const o_tease = clonedeep( o_tease_prototype );

o_tease.o_tease_classes = 'lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-u-padding-b-2 u-padding-b-075@tablet';
o_tease.o_tease_secondary_classes = 'lrv-u-flex-shrink-0 lrv-u-width-100p u-margin-r-125@tablet u-width-177@tablet u-order-n1';
o_tease.c_title.c_title_text = 'HBO Max Non-Fiction Exec Team Takes Shape With Lizzie Fox';
o_tease.c_title.c_title_classes += ' lrv-u-margin-t-050@mobile-max u-min-height-55';
o_tease.c_title.c_title_classes = o_tease.c_title.c_title_classes.replace( 'lrv-u-font-family-secondary', '' );

module.exports = {
	...o_tease
};
