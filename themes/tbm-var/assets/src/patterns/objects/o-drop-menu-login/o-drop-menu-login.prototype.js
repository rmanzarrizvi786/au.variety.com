const clonedeep = require( 'lodash.clonedeep' );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' );
const c_span = clonedeep( c_span_prototype );
const c_span_logged_in = clonedeep( c_span_prototype );

const o_nav_prototype = require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.prototype.js' );
const o_nav = clonedeep( o_nav_prototype );

const o_nav_not_logged_in_pp = clonedeep( o_nav_prototype );
const o_nav_not_logged_in_vip = clonedeep( o_nav_prototype );
const o_nav_logged_in_vip = clonedeep( o_nav_prototype );

const c_horizontal_rule = clonedeep( require( '../../components/c-horizontal-rule/c-horizontal-rule.prototype' ) );

module.exports = {
	o_drop_menu_classes: 'lrv-a-glue-parent',
	o_drop_data_attr: false,
	o_drop_menu_toggle_classes: 'js-drop-menu__toggle',
	o_drop_menu_toggle_classes_logged_in: 'js-drop-menu__toggle',
	o_drop_menu_list_classes: 'js-drop-menu__tooltip',
	c_span,
	c_span_logged_in,
	o_nav,
	c_tagline: false,
	c_link: false,
	o_icon_button: false,
	o_nav_not_logged_in_pp,
	o_nav_not_logged_in_vip,
	c_horizontal_rule,
	c_horizontal_rule_display: false,
	o_nav_logged_in_vip
};
