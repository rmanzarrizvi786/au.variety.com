<?php

use PMC\PMC_Profiles\Admin;
use PMC\PMC_Profiles\Post_Type;
use PMC\PMC_Profiles\PMC_Profiles;

$profile_highlights = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-highlights.prototype' );

if ( empty( $landing_page_details['source_taxonomy'] ) ) {
	return;
}

// TODO: Later move to fields
$profile_highlights['profile_highlights_id_attr']  = 'profile-highlights';
$profile_highlights['c_heading']['c_heading_text'] = esc_html__( 'Collectors Highlights', 'pmc-profiles' );

$source_taxonomy_slug = Admin::get_instance()->get_source_taxonomy();

$source_taxonomy_term = get_term_by( 'id', $landing_page_details['source_taxonomy'], $source_taxonomy_slug );


$carousel_data = PMC\Core\Inc\Carousels::get_instance()->get_posts(
	$source_taxonomy_term->name,
	3,
	'landscape-large',
	$source_taxonomy_slug,
	true,
	false
);

if ( empty( $carousel_data ) ) {
	return;
}

$slider_item_prototype = $profile_highlights['slider_items'][0];
$profile_slider_array  = [];


foreach ( $carousel_data as $carousel_post ) {
	$slider_item = $slider_item_prototype;

	$slider_item['c_button']['c_button_url'] = $carousel_post['url'];

	$slider_item['c_title']['c_title_text'] = $carousel_post['title'];

	$thumbnail_id = get_post_thumbnail_id( $carousel_post['ID'] );

	$secondary_taxonomy_slug  = Post_Type::get_instance()->get_taxonomy_slug( 'secondary' );
	$secondary_taxonomy_terms = PMC_Profiles::get_term_list( $carousel_post['ID'], $secondary_taxonomy_slug );

	$slider_item['c_tagline']['c_tagline_text'] = ( ! empty( $secondary_taxonomy_terms ) ) ? implode( '; ', $secondary_taxonomy_terms ) : '';

	$tertiary_taxonomy_slug   = Post_Type::get_instance()->get_taxonomy_slug( 'tertiary' );
	$quaternary_taxonomy_slug = Post_Type::get_instance()->get_taxonomy_slug( 'quaternary' );

	$tertiary_taxonomy_terms   = PMC_Profiles::get_term_list( $carousel_post['ID'], $tertiary_taxonomy_slug );
	$quaternary_taxonomy_terms = PMC_Profiles::get_term_list( $carousel_post['ID'], $quaternary_taxonomy_slug );

	$slider_item['c_tagline_second']['c_tagline_text'] = ( ! empty( $tertiary_taxonomy_terms ) ) ? implode( '; ', $tertiary_taxonomy_terms ) : '';

	$slider_item['c_dek']['c_dek_markup'] = ( ! empty( $quaternary_taxonomy_terms ) ) ? implode( '; ', $quaternary_taxonomy_terms ) : '';
	$slider_item['c_dek']['c_dek_text']   = false;


	$slider_item['c_lazy_image']['c_lazy_image_src_url']         = wp_get_attachment_image_url( $thumbnail_id, 'medium', false );
	$slider_item['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $thumbnail_id );
	$slider_item['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $thumbnail_id );
	$slider_item['c_lazy_image']['c_lazy_image_alt_attr']        = \PMC::get_attachment_image_alt_text( $thumbnail_id );
	$slider_item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();

	$profile_slider_array[] = $slider_item;
}
$profile_highlights['slider_items'] = $profile_slider_array;



\PMC::render_template(
	sprintf( '%s/build/patterns/modules/profile-highlights.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$profile_highlights,
	true
);
