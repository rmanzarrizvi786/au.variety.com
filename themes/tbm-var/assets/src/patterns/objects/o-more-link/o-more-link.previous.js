const clonedeep = require( 'lodash.clonedeep' );

const o_more_link = clonedeep( require( './o-more-link.prototype' ) );

o_more_link.c_link.c_link_classes = o_more_link.c_link.c_link_classes.replace( 'lrv-a-icon-after', 'lrv-a-icon-before' );

o_more_link.c_link.c_link_classes += ' a-icon-transform-rotate-180deg';
o_more_link.c_link.c_link_text = 'Previous';

module.exports = {
	...o_more_link
};
