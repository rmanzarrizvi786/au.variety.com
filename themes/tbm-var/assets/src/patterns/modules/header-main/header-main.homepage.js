const clonedeep = require( 'lodash.clonedeep' );

const header_main_prototype = require( './header-main.prototype.js' );
const header_main = clonedeep( header_main_prototype );

const {
	c_logo,
	o_icon_button_menu,
	expandable_search,
	o_top_nav,
} = header_main;

header_main.header_main_special_icon_classes = header_main.header_main_special_icon_classes.replace( 'u-margin-l-n60@tablet', 'u-margin-l-n60@desktop' );
header_main.c_icon.c_icon_classes = header_main.c_icon.c_icon_classes.replace( 'lrv-u-width-30', 'u-width-38' );

header_main.header_inner_classes = header_main.header_inner_classes.replace( 'u-height-90@tablet', 'u-height-132@tablet' );
header_main.header_inner_classes = header_main.header_inner_classes.replace( 'lrv-u-padding-lr-1@tablet', '' );
header_main.header_inner_classes += ' lrv-a-wrapper';

header_main.header_sticky_logo_classes = header_main.header_sticky_logo_classes.replace( 'u-max-width-205@tablet', 'u-max-width-340@tablet' );
header_main.header_sticky_logo_classes = header_main.header_sticky_logo_classes.replace( 'u-margin-l-n60@tablet', 'u-margin-l-n60@desktop' );
header_main.header_sticky_logo_classes += ' u-width-340@desktop-xl';

header_main.header_menu_icons_classes += ' lrv-u-flex-direction-column lrv-u-align-items-center u-margin-r-3@tablet';

header_main.header_navigation_classes += ' u-min-height-475@tablet';

header_main.expandable_search_wrapper_classes = header_main.expandable_search_wrapper_classes.replace( 'lrv-u-margin-r-1', 'u-margin-t-050@tablet' );

header_main.header_login_classes = header_main.header_login_classes.replace( 'lrv-u-margin-l-1', 'u-margin-l-2@tablet' );

c_logo.c_logo_classes = header_main.c_logo.c_logo_classes.replace( 'u-width-205@tablet', 'u-width-305@tablet' );
c_logo.c_logo_classes += ' u-width-340@desktop-xl';

o_icon_button_menu.o_icon_button_classes = o_icon_button_menu.o_icon_button_classes.replace( 'u-padding-l-075', 'u-padding-l-075@mobile-max' );

expandable_search.o_icon_button_search.c_icon.c_icon_classes = expandable_search.o_icon_button_search.c_icon.c_icon_classes.replace( 'lrv-u-margin-a-1', '' );

o_top_nav.o_nav_list_item_classes += ' u-margin-b-025@tablet';

module.exports = {
	...header_main,
};
