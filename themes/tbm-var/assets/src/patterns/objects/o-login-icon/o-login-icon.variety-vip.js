const clonedeep = require( 'lodash.clonedeep' );

const o_login_icon = clonedeep( require( './o-login-icon.prototype' ) );

o_login_icon.o_icon_button.c_icon.c_icon_name = 'login-outline';
o_login_icon.o_icon_button.o_icon_button_classes = o_login_icon.o_icon_button.o_icon_button_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
o_login_icon.o_icon_button.c_icon.c_icon_classes = o_login_icon.o_icon_button.c_icon.c_icon_classes.replace( 'u-color-brand-primary-40:hover', 'u-color-brand-primary:hover' );
o_login_icon.o_icon_button.o_icon_button_classes += ' a-hidden@tablet';

module.exports = o_login_icon;
