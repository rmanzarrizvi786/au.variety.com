const clonedeep = require( 'lodash.clonedeep' );
const login_actions_mobile = clonedeep( require( './login-actions-mobile.prototype' ) );

// TODO: Don't do this. Colors should not be added at the o-drop-menu level, and if tokens
// were set up, these would all be a single token that has different values for VIP and no need
// to replace them

login_actions_mobile.o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_item_classes = login_actions_mobile.o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_item_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );
login_actions_mobile.o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_item_classes = login_actions_mobile.o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_item_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );
login_actions_mobile.o_drop_menu.o_nav_logged_in_vip.o_nav_list_item_classes = login_actions_mobile.o_drop_menu.o_nav_logged_in_vip.o_nav_list_item_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );

let items = login_actions_mobile.o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items;

for ( let i = 0; i < items.length; i++ ) {
	login_actions_mobile.o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items[ i ].c_link_classes = login_actions_mobile.o_drop_menu.o_nav_not_logged_in_pp.o_nav_list_items[ i ].c_link_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );
}

items = login_actions_mobile.o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items;

for ( let j = 0; j < items.length; j++ ) {
	login_actions_mobile.o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items[ j ].c_link_classes = login_actions_mobile.o_drop_menu.o_nav_not_logged_in_vip.o_nav_list_items[ j ].c_link_classes.replace( 'u-colors-map-accent-b-100-b:hover', 'u-color-brand-primary:hover' );
}

module.exports = login_actions_mobile;
