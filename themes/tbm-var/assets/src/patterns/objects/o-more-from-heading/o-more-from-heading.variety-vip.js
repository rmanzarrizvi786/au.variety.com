const clonedeep = require( 'lodash.clonedeep' );

const o_more_from_heading_prototype = require( './o-more-from-heading.prototype.js' );
const o_more_from_heading = clonedeep( o_more_from_heading_prototype );

const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype.js' );
const c_v_icon = clonedeep( c_icon_prototype );

const {
	c_heading,
} = o_more_from_heading;

o_more_from_heading.o_more_from_heading_classes = 'lrv-a-wrapper lrv-u-flex u-justify-content-center@mobile-max lrv-u-align-items-center lrv-u-text-align-center lrv-u-padding-tb-050';

// Call c_v_icon to prevent conflicts with c_icon
c_v_icon.c_icon_classes = 'lrv-u-margin-l-025 u-height-20 u-width-70';
c_v_icon.c_icon_name = 'vip-plus';
c_v_icon.c_icon_link_screen_reader_text = 'VIP Plus';

c_heading.c_heading_classes = c_heading.c_heading_classes = 'u-text-transform-uppercase@mobile-max lrv-u-font-family-primary u-font-size-30 u-font-weight-medium u-font-weight-bold@tablet u-letter-spacing-021';
c_heading.c_heading_text = 'More From';


module.exports = {
	...o_more_from_heading,
	c_v_icon,
};
