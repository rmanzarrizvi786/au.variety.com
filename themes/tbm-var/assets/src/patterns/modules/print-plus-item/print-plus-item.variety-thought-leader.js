const clonedeep = require( 'lodash.clonedeep' );
const print_plus_item_prototype = require( './print-plus-item.prototype' );
const print_plus_item_thought_leader = clonedeep( print_plus_item_prototype );

print_plus_item_thought_leader.c_lazy_image.c_lazy_image_src_url     = 'https://pmcvariety.files.wordpress.com/2019/12/media-universe-variety-cover-story.jpg?w=1000&h=524&crop=1';
print_plus_item_thought_leader.c_lazy_image.c_lazy_image_srcset_attr = false;
print_plus_item_thought_leader.c_lazy_image.c_lazy_image_sizes_attr  = false;
print_plus_item_thought_leader.c_lazy_image.c_lazy_image_link_url    = '/thought-leaders/';

print_plus_item_thought_leader.o_more_link.c_link.c_link_text = 'Access Thought Leader';

print_plus_item_thought_leader.o_checks_list.o_checks_list_text_items = [];

print_plus_item_thought_leader.c_dek.c_dek_markup   = "Read past reports from our thought leader collection.";

print_plus_item_thought_leader.c_title.c_title_text = "Thought Leader";

print_plus_item_thought_leader.c_title.c_title_url           = '/thought-leaders/';
print_plus_item_thought_leader.o_more_link.c_link.c_link_url = '/thought-leaders/';


module.exports = print_plus_item_thought_leader;
