const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.must-read-secondary' );
const o_tease = clonedeep( o_tease_prototype );

const {
	c_title,
} = o_tease;

o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-order-n1@tablet', 'u-order-n1' );
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-padding-l-075', '' );
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'lrv-u-margin-r-1@tablet', 'lrv-u-margin-r-1' );
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-width-25p', 'u-width-65' );

c_title.c_title_classes = c_title.c_title_classes.replace( 'lrv-u-font-weight-normal', 'lrv-u-font-weight-bold' );
c_title.c_title_classes = c_title.c_title_classes.replace( 'u-font-size-15', 'u-font-size-13' );
c_title.c_title_classes = c_title.c_title_classes.replace( 'u-font-size-14@tablet', '' );
c_title.c_title_classes += ' lrv-u-font-family-secondary';

c_title.c_title_text = '‘Billions’ Creators to Develop ‘Super Pumped: The Battle for Uber’ Series at Showtime';

module.exports = {
	...o_tease,
};
