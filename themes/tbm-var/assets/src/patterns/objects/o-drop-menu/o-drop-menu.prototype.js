const clonedeep = require( 'lodash.clonedeep' );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' );
const c_span = clonedeep( c_span_prototype );

const o_nav_prototype = require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.prototype.js' );
const o_nav = clonedeep( o_nav_prototype );

module.exports = {
	o_drop_menu_classes: 'lrv-a-glue-parent',
	o_drop_data_attr: false,
	o_drop_menu_toggle_classes: 'js-drop-menu__toggle',
	o_drop_menu_list_classes: 'js-drop-menu__tooltip',
	c_span,
	o_nav,
	c_tagline: false,
	c_link: false,
	o_icon_button: false
};
