const clonedeep = require( 'lodash.clonedeep' );

const print_plus_item_prototype = require( './print-plus-item.prototype' );
const print_plus_item_variety_archives = clonedeep( print_plus_item_prototype );

print_plus_item_variety_archives.c_lazy_image.c_lazy_image_src_url     = 'https://pmcvariety.files.wordpress.com/2013/09/chinawood-cover.jpg';
print_plus_item_variety_archives.c_lazy_image.c_lazy_image_srcset_attr = false;
print_plus_item_variety_archives.c_lazy_image.c_lazy_image_sizes_attr  = false;
print_plus_item_variety_archives.c_lazy_image.c_lazy_image_link_url      = '/premier-archives-registration/';

print_plus_item_variety_archives.o_checks_list.o_checks_list_text_items = [];
print_plus_item_variety_archives.c_title.c_title_text = 'Variety Archives';

print_plus_item_variety_archives.c_title.c_title_url           = '/premier-archives-registration/';
print_plus_item_variety_archives.o_more_link.c_link.c_link_url = '/premier-archives-registration/';

print_plus_item_variety_archives.o_more_link.c_link.c_link_text = 'Access the Variety Archives';

print_plus_item_variety_archives.c_dek.c_dek_markup   = 'Access the past 15 years of editorial content through the Variety Archives'


module.exports = print_plus_item_variety_archives;
