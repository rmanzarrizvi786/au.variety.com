const clonedeep  = require( 'lodash.clonedeep' );
const header = clonedeep( require( './docs-header.prototype' ) );

header.docs_header_classes += ' lrv-u-margin-b-150';
header.c_heading.c_heading_text = 'What to Hear';
header.c_heading.c_heading_classes = 'a-font-accent-l u-font-size-52 lrv-u-line-height-small';
header.inner_docs_header_classes = 'lrv-u-flex lrv-u-width-100p lrv-u-justify-content-center u-align-items-flex-end';
header.o_sponsored_by.o_sponsored_by_classes = 'lrv-u-margin-r-075 lrv-u-flex u-align-items-flex-end u-line-height-1';
header.c_logo.c_logo_svg = 'audible-logo';
header.c_logo.c_logo_screen_reader_text = 'Audible';
header.c_logo.c_logo_classes = 'u-width-100 u-line-height-1';

module.exports = header;
