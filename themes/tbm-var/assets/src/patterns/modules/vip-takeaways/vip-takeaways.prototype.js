const clonedeep = require( 'lodash.clonedeep' );

const o_checks_list_prototype = require( '../../objects/o-checks-list/o-checks-list.prototype' );
const o_checks_list = clonedeep( o_checks_list_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' );
const c_heading = clonedeep( c_heading_prototype );

const o_check_list_item_prototype = require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' );
const o_check_list_item = clonedeep( o_check_list_item_prototype );

o_checks_list.o_checks_list_classes = 'lrv-a-unstyle-list lrv-u-padding-b-2 a-font-secondary-regular';

o_checks_list.o_checks_list_text_items = [
	o_check_list_item,
	o_check_list_item,
	o_check_list_item,
];
o_checks_list.o_checks_list_items_classes = 'lrv-u-margin-t-1 lrv-u-font-size-18 u-font-size-15@mobile-max lrv-a-icon-before a-children-icon-r-angle-quote lrv-u-flex a-children-icon-takeaway';

c_heading.c_heading_classes = 'u-font-family-accent lrv-u-text-transform-uppercase lrv-u-color-brand-primary lrv-u-font-size-12 u-letter-spacing-040';
c_heading.c_heading_text = 'In this article';

module.exports = {
	vip_takeaways_classes: 'lrv-u-margin-b-2',
	c_heading,
	o_checks_list
};
