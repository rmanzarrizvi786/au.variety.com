const clonedeep = require('lodash.clonedeep');
const header = clonedeep(require('./docs-header.prototype'));
const o_data_by = clonedeep(require('@penskemediacorp/larva-patterns/objects/o-sponsored-by/o-sponsored-by.prototype'));

const o_data_by_logo = clonedeep(require('../../components/c-logo/c-logo.prototype'));

header.docs_header_classes += ' lrv-u-margin-b-150';
header.c_heading.c_heading_text = 'Trending TV';
header.c_heading.c_heading_classes = 'a-font-accent-l u-font-size-52 lrv-u-line-height-small';
header.inner_docs_header_classes = 'lrv-u-width-100p lrv-u-text-align-center lrv-u-padding-b-050';
header.o_sponsored_by.o_sponsored_by_classes = 'lrv-u-text-align-center u-line-height-140 u-padding-b-035';
header.o_sponsored_by.o_sponsored_by_title_classes = 'u-colors-map-sponsored-90 a-font-basic-s lrv-u-text-transform-uppercase u-letter-spacing-012 u-font-size-13@tablet';

header.c_logo.c_logo_svg = 'directtv-logo';
header.c_logo.c_logo_classes = 'u-width-150 lrv-u-color-black:hover lrv-u-display-block lrv-u-margin-lr-auto';
header.c_logo.c_logo_screen_reader_text = 'Direct TV';

header.o_data_by_inner_docs_header_classes = 'u-width-400 lrv-u-border-t-1 lrv-u-border-color-grey-light lrv-u-margin-lr-auto lrv-u-padding-t-050 lrv-u-flex lrv-u-justify-content-center u-align-items-flex-center';

o_data_by.o_sponsored_by_text = 'Data Provided By';
o_data_by.o_sponsored_by_classes = 'lrv-u-margin-r-075 lrv-u-flex u-line-height-1';
o_data_by.o_sponsored_by_title_classes = 'u-colors-map-sponsored-90 a-font-basic-s lrv-u-text-transform-uppercase u-letter-spacing-012 u-font-size-13@tablet';
o_data_by.c_lazy_image = '';
o_data_by_logo.c_logo_svg = 'twitter-logo';
o_data_by_logo.c_logo_screen_reader_text = 'Twitter Logo';
o_data_by_logo.c_logo_classes = 'u-width-24 u-line-height-1';
header.o_data_by = o_data_by;
header.o_data_by_logo = o_data_by_logo;

module.exports = header;
