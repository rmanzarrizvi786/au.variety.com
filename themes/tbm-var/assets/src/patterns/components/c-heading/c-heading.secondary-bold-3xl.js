// Used for the a-font-secondary font-family on curation and widget headings, mostly homepage.
const clonedeep = require( 'lodash.clonedeep' );

const c_heading = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );

c_heading.c_heading_classes += '  a-font-secondary-bold-3xl lrv-u-padding-tb-075';

module.exports = c_heading;
