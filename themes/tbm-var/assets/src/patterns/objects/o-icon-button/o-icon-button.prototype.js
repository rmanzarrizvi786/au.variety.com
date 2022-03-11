const clonedeep = require( 'lodash.clonedeep' );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' );
const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype.js' );

const c_span = clonedeep( c_span_prototype );
const c_icon = clonedeep( c_icon_prototype );

c_icon.c_icon_name = 'hamburger';
c_icon.c_icon_url = false;

c_span.c_span_text = 'Menu';
c_span.c_span_classes = 'lrv-u-margin-l-050';
c_span.c_span_url = false;

module.exports = {
	o_icon: true,
	o_icon_button_classes: 'lrv-a-unstyle-button lrv-a-unstyle-link lrv-u-cursor-pointer lrv-u-flex',
	o_icon_button_screen_reader_text: '',
	is_vip_plus_animated: false,
	c_icon: c_icon,
	c_span: c_span,
};