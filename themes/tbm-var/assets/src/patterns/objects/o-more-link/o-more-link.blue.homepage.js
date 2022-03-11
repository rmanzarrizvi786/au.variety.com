const clonedeep = require( 'lodash.clonedeep' );

const o_more_link = clonedeep( require( './o-more-link.blue' ) );

o_more_link.o_more_link_classes += ' lrv-u-text-align-right lrv-u-padding-tb-075 lrv-u-border-t-1 u-border-color-brand-secondary-40';

module.exports = o_more_link;
