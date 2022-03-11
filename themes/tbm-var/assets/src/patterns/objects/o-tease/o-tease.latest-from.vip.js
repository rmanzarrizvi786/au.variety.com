const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.latest-from.primary' );
const o_tease = clonedeep( o_tease_prototype );

const {
	c_lazy_image,
	c_title,
	c_span,
	c_link,
	c_timestamp,
} = o_tease;

o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'u-padding-b-075', 'lrv-u-padding-b-1' );
o_tease.o_tease_classes = o_tease.o_tease_classes.replace( 'u-padding-b-075@tablet', '' );
o_tease.o_tease_classes += ' lrv-u-border-b-1  u-border-color-brand-secondary-40';

o_tease.o_tease_primary_classes = o_tease.o_tease_primary_classes.replace( 'u-padding-lr-075@mobile-max', '' );

c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/605x413';
c_lazy_image.c_lazy_image_classes = c_lazy_image.c_lazy_image_classes.replace( 'u-border-b-6@tablet', '' );
c_lazy_image.c_lazy_image_classes += ' u-margin-lr-n075@mobile-max';
c_lazy_image.c_lazy_image_crop_class = c_lazy_image.c_lazy_image_crop_class.replace( 'a-crop-200x133@tablet', 'a-crop-605x413@tablet' );

c_title.c_title_classes = c_title.c_title_classes.replace( 'lrv-u-font-size-24', 'u-font-size-22' );
c_title.c_title_classes = c_title.c_title_classes.replace( 'u-font-size-30@tablet', 'u-font-size-24@tablet' );
c_title.c_title_classes = c_title.c_title_classes.replace( 'lrv-u-margin-t-050@mobile-max', 'u-margin-t-075' );
c_title.c_title_classes = c_title.c_title_classes.replace( 'u-font-family-basic', 'lrv-u-font-family-primary' );
c_title.c_title_classes += ' lrv-u-font-weight-normal';
c_title.c_title_text = 'Legalized Betting On Sports Hits The One-Year Mark: U.S. Media Bӏitz  In It to Win It';
c_title.c_title_link_classes = 'lrv-u-color-black lrv-u-display-block u-color-brand-secondary-50:hover';

c_span.c_span_classes = 'lrv-u-display-none';

c_link.c_link_classes = 'lrv-u-display-none';

c_timestamp.c_timestamp_classes = 'lrv-u-display-none';

module.exports = {
	...o_tease,
};
