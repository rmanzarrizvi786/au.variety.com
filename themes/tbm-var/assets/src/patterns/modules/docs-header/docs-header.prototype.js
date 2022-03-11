const clonedeep = require( 'lodash.clonedeep' );

const c_heading = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );
c_heading.c_heading_classes = 'a-font-accent-l u-font-size-52 u-line-height-120';
c_heading.c_heading_text = 'Docs';

const o_sponsored_by = clonedeep( require( '@penskemediacorp/larva-patterns/objects/o-sponsored-by/o-sponsored-by.prototype' ) );

const c_logo = clonedeep( require( '../../components/c-logo/c-logo.prototype' ) );

c_logo.c_logo_svg = 'showtime-documentary-films';
c_logo.c_logo_classes = 'u-width-200 lrv-u-color-black:hover';
c_logo.c_logo_screen_reader_text = 'Showtime Documentary Films';
c_logo.c_logo_target_attr = '_blank';

o_sponsored_by.o_sponsored_by_classes = 'lrv-u-margin-b-025';
o_sponsored_by.o_sponsored_by_title_classes = 'u-colors-map-sponsored-90 a-font-basic-s lrv-u-text-transform-uppercase u-letter-spacing-004 u-font-size-13@tablet';
o_sponsored_by.o_sponsored_by_text = 'Powered by';
o_sponsored_by.c_lazy_image = false;

module.exports = {
	docs_header_classes: 'lrv-u-padding-t-1',
	inner_docs_header_classes: 'lrv-u-flex lrv-u-width-100p lrv-u-justify-content-center lrv-u-align-items-center lrv-u-flex-direction-column',
	c_heading,
	o_sponsored_by,
	c_logo,
};
