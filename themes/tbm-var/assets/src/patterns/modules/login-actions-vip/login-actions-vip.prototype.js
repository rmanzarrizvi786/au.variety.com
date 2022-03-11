const clonedeep = require( 'lodash.clonedeep' );
const header_login_button = clonedeep( require( '../header-button/header-button.variety-vip' ) );
const cxense_header_subscribe_widget = clonedeep( require( '../cxense-widget/cxense-widget.prototype' ) );
const o_drop_menu = clonedeep( require( '../../objects/o-drop-menu/o-drop-menu.prototype' ) );
const o_login_icon = clonedeep( require( '../../objects/o-login-icon/o-login-icon.variety-vip' ) );

const c_span_user = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' ) );
const c_tagline = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype.js' ) );

header_login_button.c_span_main.c_span_text = 'Log in';
header_login_button.header_button_url = '/digital-subscriber-access/#r=/print-plus/';
header_login_button.header_button_classes += ' a-hidden@mobile-max';
header_login_button.c_span_main.c_span_classes += ' lrv-u-text-transform-uppercase lrv-u-color-black u-color-brand-primary:hover lrv-u-display-inline-block lrv-u-width-100';

cxense_header_subscribe_widget.cxense_id_attr = 'cx-module-header-link-vip';

o_drop_menu.o_drop_menu_classes += ' lrv-u-whitespace-nowrap lrv-u-border-color-grey lrv-u-font-family-secondary';
o_drop_menu.c_span.c_span_text = 'Account';
o_drop_menu.c_span.c_span_classes = 'lrv-u-text-transform-uppercase lrv-u-font-weight-bold lrv-u-font-size-12 lrv-u-color-black u-letter-spacing-001 lrv-u-margin-l-050 u-color-brand-primary:hover';
o_drop_menu.o_drop_menu_list_classes += ' lrv-a-glue lrv-a-glue--r-0 lrv-u-width-300 lrv-u-background-color-white lrv-u-border-a-1 lrv-u-border-color-grey-light lrv-u-padding-a-1';

c_span_user.c_span_classes = 'lrv-u-font-size-16 lrv-u-font-weight-bold lrv-u-text-transform-uppercase lrv-u-color-grey-dark';
c_span_user.c_span_text = 'Awallenstein';

c_tagline.c_tagline_classes = 'lrv-u-margin-tb-00 lrv-u-padding-t-025 lrv-u-padding-b-1 lrv-u-font-size-14 lrv-u-color-grey-dark';
c_tagline.c_tagline_text = 'Variety Print Plus Subscriber';

o_drop_menu.c_span_user = c_span_user;
o_drop_menu.c_tagline = c_tagline;

o_drop_menu.o_nav.o_nav_list_item_classes += ' lrv-u-font-size-14 lrv-u-font-weight-bold lrv-u-color-grey-dark lrv-u-padding-b-050 u-color-brand-primary:hover lrv-u-display-block';

module.exports = {
	o_login_icon,
	header_login_button,
	cxense_header_subscribe_widget,
	o_drop_menu
};
