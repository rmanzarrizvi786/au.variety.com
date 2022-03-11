const clonedeep = require( 'lodash.clonedeep' );
const login_actions = clonedeep( require( './login-actions.prototype' ) );
const header_button_vip = clonedeep( require( '../header-button/header-button.variety-vip' ) );
const o_login_icon_vip = clonedeep( require( '../../objects/o-login-icon/o-login-icon.variety-vip' ) );

login_actions.header_login_button.header_button_classes = header_button_vip.header_button_classes;

login_actions.o_login_icon = o_login_icon_vip;

// TODO: Don't do this. Colors should not be added at the o-drop-menu level, and if tokens
// were set up, these would all be a single token that has different values for VIP and no need
// to replace them

login_actions.o_drop_menu.c_span.c_span_classes = login_actions.o_drop_menu.c_span.c_span_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
login_actions.o_drop_menu.c_span.c_span_classes = login_actions.o_drop_menu.c_span.c_span_classes.replace( 'u-color-brand-primary-40:hover', 'u-color-brand-primary:hover' );

login_actions.o_drop_menu_mobile_icon.o_icon_button.o_icon_button_classes = login_actions.o_drop_menu_mobile_icon.o_icon_button.o_icon_button_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
login_actions.o_drop_menu_mobile_icon.o_icon_button.c_icon.c_icon_classes = login_actions.o_drop_menu_mobile_icon.o_icon_button.c_icon.c_icon_classes.replace( 'u-color-brand-primary-40:hover', 'u-color-brand-primary:hover' );

let menus = [ 'o_drop_menu', 'o_drop_menu_mobile_icon' ];

for ( let i = 0; i < menus.length; i++ ) {
	login_actions[ menus[ i ] ].c_span_logged_in.c_span_classes = login_actions[ menus[ i ] ].c_span_logged_in.c_span_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
	login_actions[ menus[ i ] ].c_span_logged_in.c_span_classes = login_actions[ menus[ i ] ].c_span_logged_in.c_span_classes.replace( 'u-color-brand-primary-40:hover', 'u-color-brand-primary:hover' );

	login_actions[ menus[ i ] ].o_nav_not_logged_in_pp.o_nav_list_item_classes = login_actions[ menus[ i ] ].o_nav_not_logged_in_pp.o_nav_list_item_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );

	let items = login_actions[ menus[ i ] ].o_nav_not_logged_in_pp.o_nav_list_items;

	for ( let j = 0; j < items.length; j++ ) {
		login_actions[ menus[ i ] ].o_nav_not_logged_in_pp.o_nav_list_items[ j ].c_link_classes = login_actions[ menus[ i ] ].o_nav_not_logged_in_pp.o_nav_list_items[ j ].c_link_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );
	}

	login_actions[ menus[ i ] ].o_nav_not_logged_in_vip.o_nav_list_item_classes = login_actions[ menus[ i ] ].o_nav_not_logged_in_vip.o_nav_list_item_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );

	items = login_actions[ menus[ i ] ].o_nav_not_logged_in_vip.o_nav_list_items;

	for ( let k = 0; k < items.length; k++ ) {
		login_actions[ menus[ i ] ].o_nav_not_logged_in_vip.o_nav_list_items[ k ].c_link_classes = login_actions[ menus[ i ] ].o_nav_not_logged_in_vip.o_nav_list_items[ k ].c_link_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );
	}

	login_actions[ menus[ i ] ].o_nav_logged_in_vip.o_nav_list_item_classes = login_actions[ menus[ i ] ].o_nav_logged_in_vip.o_nav_list_item_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );

	login_actions[ menus[ i ] ].o_drop_menu_list_classes = login_actions[ menus[ i ] ].o_drop_menu_list_classes.replace( 'u-background-color-accent-c-40', '' )
	login_actions[ menus[ i ] ].o_nav.o_nav_list_item_classes = login_actions[ menus[ i ] ].o_nav.o_nav_list_item_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );
}

module.exports = login_actions;
