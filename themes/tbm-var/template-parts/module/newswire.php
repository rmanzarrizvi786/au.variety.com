<?php
/**
 * More From Brands module.
 *
 * @package pmc-variety;
 */


$newswire = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/newswire.prototype' );

$feeds = PMC\Core\Inc\Footer_Feed::get_instance()->get_json_data( [], 'pmc-footer' );

if ( empty( $feeds ) || ! is_array( $feeds ) ) {
	return;
}

// Save template
$o_tease_list_item_template = $newswire['o_tease_list']['o_tease_list_items'][0];

// Reset array
$newswire['o_tease_list']['o_tease_list_items'] = [];

foreach ( (array) $feeds as $feed ) {
	if ( is_array( (array) $feed ) ) {

		// Copy template
		$o_tease_list_item = $o_tease_list_item_template;

		// Setup o_tease_list_item
		$o_tease_list_item['c_title']['c_title_text']                  = $feed['title'];
		$o_tease_list_item['c_title']['c_title_url']                   = $feed['url'];
		$o_tease_list_item['c_lazy_image']['c_lazy_image_src_url']     = $feed['image'];
		$o_tease_list_item['c_lazy_image']['c_lazy_image_link_url']    = $feed['url'];
		$o_tease_list_item['c_lazy_image']['c_lazy_image_srcset_attr'] = null;
		// empty alt string for newswire images marks them as decorative
		// and removes then from screen readers.
		$o_tease_list_item['c_lazy_image']['c_lazy_image_alt_attr']        = '';
		$o_tease_list_item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$o_tease_list_item['c_span']['c_span_text']                        = $feed['source']['name'];
		$o_tease_list_item['c_span']['c_span_url']                         = $feed['url'];

		// Add to o_tease_list_items
		$newswire['o_tease_list']['o_tease_list_items'][] = $o_tease_list_item;

	}
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/newswire.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$newswire,
	true
);
