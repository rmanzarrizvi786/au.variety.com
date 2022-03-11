const clonedeep = require( 'lodash.clonedeep' );

const c_heading = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );
c_heading.c_heading_classes = 'a-font-secondary-bold u-font-size-48@tablet lrv-u-font-size-36 lrv-u-line-height-small u-letter-spacing-001 lrv-u-margin-b-050';
c_heading.c_heading_text = 'What to Watch';

const c_tagline = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' ) );

c_tagline.c_tagline_text = 'We picked the very best movies and TV shows to stream right now so you don’t have to.';
c_tagline.c_tagline_classes = 'a-font-secondary-regural lrv-u-font-size-14@mobile-max lrv-u-font-size-18 lrv-u-text-align-center u-letter-spacing-0002 lrv-u-margin-t-00 lrv-u-margin-b-075';

const o_sponsored_by = clonedeep( require( '@penskemediacorp/larva-patterns/objects/o-sponsored-by/o-sponsored-by.prototype' ) );

const c_logo = clonedeep( require( '../../components/c-logo/c-logo.prototype' ) );

c_logo.c_logo_svg = 'apple-tv-plus-updated';
c_logo.c_logo_classes = 'u-width-70';
c_logo.c_logo_screen_reader_text = 'Apple TV Plus';
c_logo.c_logo_target_attr = '_blank';

o_sponsored_by.o_sponsored_by_classes = 'lrv-u-margin-r-1@tablet';
o_sponsored_by.o_sponsored_by_title_classes = 'u-colors-map-sponsored-90 a-font-basic-s lrv-u-text-transform-uppercase u-letter-spacing-004 u-font-size-13@tablet';
o_sponsored_by.o_sponsored_by_text = 'Powered by';
o_sponsored_by.c_lazy_image = false;

module.exports = {
	hub_header_classes: 'lrv-a-wrapper lrv-u-margin-tb-150@mobile-max u-margin-tb-250@tablet',
	c_heading,
	c_tagline,
	o_sponsored_by,
	c_logo,
};
