const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( './o-tease.prototype.js' );
const o_tease = clonedeep( o_tease_prototype );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' );
const c_span = clonedeep( c_span_prototype );

const { c_title, c_lazy_image, c_link } = o_tease;

o_tease.o_tease_classes = 'lrv-u-flex lrv-u-flex-direction-column@tablet lrv-u-padding-b-1 u-padding-t-075 u-padding-t-00@tablet u-padding-b-125@tablet';
o_tease.o_tease_secondary_classes = o_tease.o_tease_secondary_classes.replace( 'u-width-177@tablet', 'lrv-u-width-100p' );

c_lazy_image.c_lazy_image_crop_class += ' a-crop-258x125@tablet';

c_span.c_span_classes = 'a-hidden@mobile-max';
c_span.c_span_url = '#';
c_span.c_span_text = 'Digital';
c_span.c_span_link_classes = `${o_tease.c_link.c_link_classes} u-margin-t-1@tablet u-margin-b-050@tablet`;

c_link.c_link_text = 'By Kevin Tran';
c_link.c_link_classes = c_link.c_link_classes.replace( 'lrv-u-text-transform-uppercase', '' );
c_link.c_link_classes += ' u-letter-spacing-005@tablet u-margin-t-050@tablet';

module.exports = {
	...o_tease,
	c_span,
};
