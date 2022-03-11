const clonedeep = require( 'lodash.clonedeep' );

const o_more_link_prototype = require( './o-more-link.prototype' );
const o_more_link = clonedeep( o_more_link_prototype );

o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'a-icon-long-right-arrow', 'a-icon-long-right-arrow-blue' );
o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'u-color-brand-secondary-50', 'u-color-pale-sky-2' );

module.exports = {
	...o_more_link,
};
