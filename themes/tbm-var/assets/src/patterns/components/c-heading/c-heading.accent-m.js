// Used for the a-font-accent font-family on curation and widget headings on the homepage and in sidebars.
const clonedeep = require( 'lodash.clonedeep' );

const c_heading = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );

c_heading.c_heading_classes = 'a-font-accent-m lrv-u-text-align-center lrv-u-padding-tb-075 u-padding-t-025@tablet';

module.exports = c_heading;
