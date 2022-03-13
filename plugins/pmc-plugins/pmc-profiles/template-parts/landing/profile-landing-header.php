<?php

$profile_landing_header = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-landing-header.prototype' );

$profile_landing_header['profile_body']['c_heading']['c_heading_text'] = pmc_get_title();

$profile_landing_header['profile_body']['c_dek']['c_dek_markup'] = pmc_get_excerpt();

$profile_landing_header['profile_body']['profile_body_content_markup'] = get_the_content();

$thumbnail_id = get_post_thumbnail_id();
$profile_landing_header['c_lazy_image']['c_lazy_image_src_url']         = wp_get_attachment_image_url( $thumbnail_id, 'landscape-large', false );
$profile_landing_header['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $thumbnail_id );
$profile_landing_header['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $thumbnail_id );
$profile_landing_header['c_lazy_image']['c_lazy_image_alt_attr']        = \PMC::get_attachment_image_alt_text( $thumbnail_id );
$profile_landing_header['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();


$profile_landing_header['c_button']['c_button_text'] = $landing_page_details['explore_list_button_text'];
$profile_landing_header['c_button']['c_button_url']  = $landing_page_details['explore_list_button_url'];

\PMC::render_template(
	sprintf( '%s/build/patterns/modules/profile-landing-header.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$profile_landing_header,
	true
);
