const clonedeep = require( 'lodash.clonedeep' );

const print_plus_item_prototype    = require( './print-plus-item.prototype' );
const print_plus_item_digital_copy = clonedeep( print_plus_item_prototype );

print_plus_item_digital_copy.c_lazy_image.c_lazy_image_srcset_attr = false;
print_plus_item_digital_copy.c_lazy_image.c_lazy_image_sizes_attr  = false;
print_plus_item_digital_copy.c_lazy_image.c_lazy_image_src_url     = 'https://pmcvariety.files.wordpress.com/2020/01/taylor-swift-sundance-variety-cover-700.jpg';
print_plus_item_digital_copy.c_lazy_image.c_lazy_image_link_url    = '/access-digital';

print_plus_item_digital_copy.c_title.c_title_text           = 'Variety Digital Edition';
print_plus_item_digital_copy.c_dek.c_dek_markup             = 'Browse or search current and past digital issues of Variety Magazine including:';
print_plus_item_digital_copy.o_more_link.c_link.c_link_text = 'Access My Digital Issues';
print_plus_item_digital_copy.c_title.c_title_url            = '/access-digital/';
print_plus_item_digital_copy.o_more_link.c_link.c_link_url  = '/access-digital/';

const o_check_list_item_prototype     = require( '../../objects/o-checks-list-item/o-checks-list-item.prototype' );
const o_check_list_item_1             = clonedeep( o_check_list_item_prototype );
o_check_list_item_1.o_check_list_text = 'Regular Issues';
const o_check_list_item_2             = clonedeep( o_check_list_item_prototype );
o_check_list_item_2.o_check_list_text = 'Special Issues';

print_plus_item_digital_copy.o_checks_list.o_checks_list_text_items = [
	o_check_list_item_1,
	o_check_list_item_2
];

module.exports = print_plus_item_digital_copy;
