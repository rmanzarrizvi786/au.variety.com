const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( '../../objects/o-more-from-heading/o-more-from-heading.variety-vip.js');
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const {
	c_heading,
	c_v_icon,
} = o_more_from_heading;

o_more_from_heading.o_more_from_heading_classes = 'lrv-a-wrapper lrv-u-flex lrv-u-justify-content-center lrv-u-align-items-center lrv-u-text-align-center a-hidden@tablet lrv-u-padding-tb-050';

c_heading.c_heading_classes = 'lrv-u-text-transform-uppercase lrv-u-font-family-primary lrv-u-font-weight-normal u-font-size-30 u-letter-spacing-040';
c_heading.c_heading_text = 'More Video From';

c_v_icon.c_icon_classes = 'lrv-u-margin-l-025 u-height-20 u-width-70';
c_v_icon.c_icon_name = 'vip-plus';
c_v_icon.c_icon_link_screen_reader_text = 'VIP Plus';

module.exports = {
	o_more_from_heading
};
