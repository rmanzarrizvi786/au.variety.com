const clonedeep = require( 'lodash.clonedeep' );

const o_custom_paragraph_prototype = require( './o-custom-paragraph.prototype' );
const o_custom_paragraph = clonedeep( o_custom_paragraph_prototype );

o_custom_paragraph.o_custom_paragraph_classes += ' lrv-u-font-size-14 lrv-u-font-family-secondary u-font-size-16@tablet u-line-height-small@tablet lrv-u-margin-tb-050';

module.exports = {
	...o_custom_paragraph
};
