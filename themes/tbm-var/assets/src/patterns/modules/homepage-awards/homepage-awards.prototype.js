const clonedeep = require( 'lodash.clonedeep' );

const awards_curated_prototype = require( '../homepage-horizontal-block/homepage-horizontal-block.prototype' );
const awards_curated = clonedeep( awards_curated_prototype );

awards_curated.c_span = false;
awards_curated.o_more_from_heading.c_heading.c_heading_text = 'Awards';


module.exports = {
	homepage_awards_classes: 'lrv-a-wrapper lrv-a-grid u-grid-gap-0 u-grid-gap-1@desktop-xl',
	awards_curated,
};
