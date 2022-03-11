const clonedeep = require( 'lodash.clonedeep' );
const c_link = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' ) );

c_link.c_link_classes = 'lrv-u-color-white lrv-u-font-size-16 lrv-u-font-size-14@tablet u-font-family-body u-letter-spacing-005';

const o_nav_list_items = [
	clonedeep( c_link ),
	clonedeep( c_link ),
	clonedeep( c_link ),
	clonedeep( c_link ),
	clonedeep( c_link ),
];

module.exports = {
	modifier_class: '',
	o_nav_classes: '',
	o_nav_title_text: '',
	o_nav_title_classes: '',
	o_nav_list_classes: 'lrv-a-unstyle-list',
	o_nav_list_item_classes: '',
	o_nav_list_items: o_nav_list_items,
	o_structured_data: true,
};
