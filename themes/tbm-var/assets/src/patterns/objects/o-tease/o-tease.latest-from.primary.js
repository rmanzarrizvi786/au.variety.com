const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.primary.js' );
const o_tease = clonedeep( o_tease_prototype );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' );
const c_span = clonedeep( c_span_prototype );

const { c_title, c_lazy_image, c_link } = o_tease;

o_tease.o_tease_classes = 'lrv-u-flex lrv-u-flex-direction-column u-padding-b-075 u-padding-b-075@tablet';

o_tease.o_tease_primary_classes += ' u-padding-lr-075@mobile-max';

o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-width-177@tablet', '' );

c_title.c_title_text = 'Streaming Royalties at Heart of Class-Action Lawsuit Against Spotify';
c_title.c_title_classes = c_title.c_title_classes.replace( 'u-font-size-15', 'lrv-u-font-size-24' );
c_title.c_title_classes = c_title.c_title_classes.replace( 'lrv-u-margin-t-050@mobile-max', 'u-margin-t-075' );
c_title.c_title_classes = c_title.c_title_classes.replace( 'u-font-size-16@tablet', 'u-font-size-30@tablet' );
c_title.c_title_classes += ' lrv-u-font-weight-normal u-font-family-basic';

c_lazy_image.c_lazy_image_classes = '';
c_lazy_image.c_lazy_image_crop_class += ' a-crop-200x133@tablet';

c_span.c_span_classes = 'a-hidden@mobile-max';
c_span.c_span_url = '#';
c_span.c_span_text = 'Film';
c_span.c_span_link_classes = `${o_tease.c_link.c_link_classes} u-margin-t-150@tablet`;

c_link.c_link_text = 'By Andrew Wallenstein';
c_link.c_link_classes = c_link.c_link_classes.replace( 'lrv-u-text-transform-uppercase', '' );
c_link.c_link_classes += ' u-letter-spacing-005@tablet u-margin-t-050@tablet';

module.exports = {
	...o_tease,
	c_span,
};
