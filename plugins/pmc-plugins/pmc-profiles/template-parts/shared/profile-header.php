<?php

$profile_header = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-header.prototype' );

$profile_settings = get_option( 'profiles_sponsor_settings' );

$profile_header['profile_header_title_text'] = $profile_settings['sponsor_header_title'];

if ( ! empty( $profile_settings['sponsor_logo'] ) ) {
	$profile_header['o_sponsored_by']['o_sponsored_by_text']                          = esc_html__( 'Sponsored By', 'pmc-profiles' );
	$profile_header['o_sponsored_by']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$profile_header['o_sponsored_by']['c_lazy_image']['c_lazy_image_src_url']         = \wp_get_attachment_image_url( $profile_settings['sponsor_logo'] );
	$profile_header['o_sponsored_by']['c_lazy_image']['c_lazy_image_alt_attr']        = \PMC::get_attachment_image_alt_text( $profile_settings['sponsor_logo'] );
	$profile_header['o_sponsored_by']['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $profile_settings['sponsor_logo'] );
	$profile_header['o_sponsored_by']['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $profile_settings['sponsor_logo'] );
} else {
	$profile_header['o_sponsored_by'] = false;
}

$header_data = PMC\Core\Inc\Menu::get_instance()->get_menu_data( 'pmc_profiles_header' );

$nav_list_item_prototype = $profile_header['o_nav']['o_nav_list_items'][0];

$nav_list_items_array   = [];
$select_list_item_array = [];

if ( is_array( $header_data ) && isset( $header_data['root'] ) ) {

	foreach ( $header_data['root'] as $menu_data_item ) {
		$nav_item = $nav_list_item_prototype;

		$has_select_class = false;

		if ( isset ( $menu_data_item['classes'] ) ) {
			$has_select_class = strrpos( $menu_data_item['classes'], 'select_menu' );
		}

		$select_item_prototypye = $profile_header['o_select_nav']['o_select_nav_options'][0];


		if ( false !== $has_select_class && ! empty( $menu_data_item['child'] && is_array( $header_data ) ) ) {
			$profile_header['o_select_nav']['c_button']['c_button_text'] = $menu_data_item['text'];
			$profile_header['o_select_nav']['c_button']['c_button_url']  = $menu_data_item['url'];

			foreach ( $menu_data_item['child'] as $child ) {
				$select_item = $select_item_prototypye;

				$select_item['c_select_option_url']   = $child['url'];
				$select_item['c_select_option_value'] = $child['url'];
				$select_item['c_select_option_text']  = $child['text'];

				$select_list_item_array[] = $select_item;
			}
		} else {

			if ( isset( $menu_data_item['text'] ) && isset( $menu_data_item['url'] ) ) {
				$nav_item['c_link_text'] = $menu_data_item['text'];
				$nav_item['c_link_url']  = $menu_data_item['url'];
			} else {
				$nav_item['c_link_text'] = '';
				$nav_item['c_link_url']  = '';
			}

			$nav_list_items_array[] = $nav_item;
		}
	}
}

$profile_header['o_nav']['o_nav_list_items']            = $nav_list_items_array;
$profile_header['o_select_nav']['o_select_nav_options'] = $select_list_item_array;

\PMC::render_template(
	sprintf( '%s/build/patterns/modules/profile-header.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$profile_header,
	true
);
