<?php

use PMC\PMC_Profiles\Admin;

$profile_browse_by = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-browse-by.prototype' );

$category_list_item_array = [];

if ( ! empty( $landing_page_details['category_list'] && is_array( $landing_page_details['category_list'] ) ) ) {
	$card_item_prototype = $profile_browse_by['o_card_list']['o_card_list_items'][0];

	foreach ( $landing_page_details['category_list'] as $category_id ) {
		$category_taxonomy = Admin::get_instance()->get_category_taxonomy();
		$category          = get_term_by( 'id', $category_id['category'], $category_taxonomy );

		$card_list_item = $card_item_prototype;

		$category_icon = get_term_meta( $category_id['category'], 'category_icon', true );

		if ( ! empty( $category_icon ) ) {
			$card_list_item['c_lazy_image']['c_lazy_image_src_url']         = wp_get_attachment_image_url( $category_icon, 'medium', false );
			$card_list_item['c_lazy_image']['c_lazy_image_srcset_attr']     = false;
			$card_list_item['c_lazy_image']['c_lazy_image_sizes_attr']      = false;
			$card_list_item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$card_list_item['c_lazy_image']['c_lazy_image_alt_attr']        = \PMC::get_attachment_image_alt_text( $category_icon );
		} else {
			$card_list_item['c_lazy_image'] = false;
		}

		$card_list_item['c_title']['c_title_text'] = $category->name;

		$category_list_item_array[] = $card_list_item;
	}

	$profile_browse_by['o_card_list']['o_card_list_items'] = $category_list_item_array;

}

if ( ! empty( $category_list_item_array ) ) {

	\PMC::render_template(
		sprintf( '%s/build/patterns/modules/profile-browse-by.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
		$profile_browse_by,
		true
	);

}
