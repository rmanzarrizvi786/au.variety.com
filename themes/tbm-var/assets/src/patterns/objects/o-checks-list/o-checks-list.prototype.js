const clonedeep = require( 'lodash.clonedeep' );

const o_check_list_item_prototype = require( '../o-checks-list-item/o-checks-list-item.prototype' );
const o_check_list_item = clonedeep( o_check_list_item_prototype );

o_checks_list_text_items = [
	o_check_list_item,
	o_check_list_item,
	o_check_list_item,
];

module.exports = {
	o_checks_list_classes: 'u-font-family-basic u-color-pale-sky-2 lrv-u-font-size-12 u-font-size-15@tablet lrv-a-unstyle-list lrv-u-border-t-1 u-border-dotted-t u-border-color-secondary-80 ',
	o_checks_list_items_classes: 'lrv-u-padding-tb-050 lrv-u-border-b-1 u-border-dotted-b u-border-color-secondary-80 a-icon-checkmark lrv-a-icon-before lrv-u-flex ',
	o_checks_list_text_items
};
