const clonedeep = require( 'lodash.clonedeep' );

const o_checks_list_prototype = require( '../../objects/o-checks-list/o-checks-list.prototype' );
const o_checks_list = clonedeep( o_checks_list_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' );
const c_heading = clonedeep( c_heading_prototype );

const o_check_list_item_prototype = require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' );
const o_check_list_item = clonedeep( o_check_list_item_prototype );

o_checks_list.o_checks_list_classes = 'lrv-a-unstyle-list lrv-u-border-t-1 u-border-dotted-t u-border-color-secondary-80 lrv-u-margin-b-2 a-font-secondary-bold-s';

o_checks_list.o_checks_list_text_items = [
  o_check_list_item,
  o_check_list_item,
  o_check_list_item,
];
o_checks_list.o_checks_list_classes += 'lrv-u-margin-b-2';

c_heading.c_heading_classes = 'lrv-u-font-family-secondary lrv-u-font-size-18 u-font-size-21@tablet lrv-u-margin-tb-050 u-margin-b-075@tablet';
c_heading.c_heading_text = 'Key Takeaways:';

module.exports = {
	c_heading,
	o_checks_list
};
