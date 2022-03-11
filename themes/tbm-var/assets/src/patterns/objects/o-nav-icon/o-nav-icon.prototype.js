const clonedeep = require( 'lodash.clonedeep' );
const o_icon_button_prototype = require( '../o-icon-button/o-icon-button.prototype.js' );
const o_icon_button = clonedeep( o_icon_button_prototype );

o_icon_button.o_icon_button_classes = 'lrv-u-color-white lrv-u-font-size-16 lrv-u-font-size-14@tablet u-font-family-body u-letter-spacing-005 u-color-variety-primary:hover';

const o_nav_list_items = [
	clonedeep( o_icon_button ),
	clonedeep( o_icon_button ),
	clonedeep( o_icon_button ),
	clonedeep( o_icon_button ),
	clonedeep( o_icon_button ),
];

module.exports = {
	"modifier_class": "",
	"o_nav_classes": "",
	"o_nav_title_text": "",
	"o_nav_title_classes": "",
	"o_nav_list_classes" : "lrv-a-unstyle-list",
	"o_nav_list_item_classes": "",
	"o_nav_list_items": o_nav_list_items
};
