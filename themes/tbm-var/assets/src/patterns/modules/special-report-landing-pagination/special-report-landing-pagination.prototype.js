const clonedeep = require( 'lodash.clonedeep' );

const o_more_link = clonedeep( require( '../../objects/o-more-link/o-more-link.prototype.js' ) );
const o_more_link_previous = clonedeep( require( '../../objects/o-more-link/o-more-link.previous.js' ) );

o_more_link.o_more_link_classes = 'lrv-u-margin-l-auto';

module.exports = {
	special_report_landing_pagination_is_paged: false,
	o_more_link,
	o_more_link_previous
};
