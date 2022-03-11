const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

c_heading.c_heading_classes = 'lrv-u-font-family-primary u-font-weight-medium lrv-u-line-height-small ';
c_heading.c_heading_text = 'o-title';
c_heading.c_heading_is_primary_heading = true;

module.exports = {
	c_heading
};
