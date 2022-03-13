<?php

// We will need to incorporate a template for each card, but for now, stubbing this as is
use PMC\PMC_Profiles\PMC_Profiles;
use PMC\PMC_Profiles\Post_Type;

$profile_card_list = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-card-list.prototype' );

$profile_card_prototype = $profile_card_list['profile_card_list'][0];

$profile_card_array = [];

while ( have_posts() ) {
	the_post();

	$profile_card_item = $profile_card_prototype;

	$profile_card_item['c_title']['c_title_text'] = pmc_get_title();
	$profile_card_item['c_title']['c_title_url']  = get_permalink();

	$secondary_taxonomy_slug = Post_Type::get_instance()->get_taxonomy_slug( 'secondary' );

	$secondary_taxonomy_terms = PMC_Profiles::get_term_list( get_the_ID(), $secondary_taxonomy_slug );

	$profile_card_item['c_tagline']['c_tagline_text'] = ( ! empty( $secondary_taxonomy_terms ) ) ? implode( '; ', $secondary_taxonomy_terms ) : '';

	$tertiary_taxonomy_slug   = Post_Type::get_instance()->get_taxonomy_slug( 'tertiary' );
	$quaternary_taxonomy_slug = Post_Type::get_instance()->get_taxonomy_slug( 'quaternary' );

	$tertiary_taxonomy_terms   = PMC_Profiles::get_term_list( get_the_ID(), $tertiary_taxonomy_slug );
	$quaternary_taxonomy_terms = PMC_Profiles::get_term_list( get_the_ID(), $quaternary_taxonomy_slug );

	$profile_card_item['c_tagline_second']['c_tagline_text'] = ( ! empty( $tertiary_taxonomy_terms ) ) ? implode( '; ', $tertiary_taxonomy_terms ) : '';

	$profile_card_item['c_dek']['c_dek_markup'] = ( ! empty( $quaternary_taxonomy_terms ) ) ? implode( '; ', $quaternary_taxonomy_terms ) : '';
	$profile_card_item['c_dek']['c_dek_text']   = false;

	$profile_card_item['c_button']['c_button_url'] = get_permalink();

	$thumbnail_id = get_post_thumbnail_id( get_the_ID() );

	$profile_card_item['c_lazy_image']['c_lazy_image_src_url']         = wp_get_attachment_image_url( $thumbnail_id, 'medium', false );
	$profile_card_item['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $thumbnail_id );
	$profile_card_item['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $thumbnail_id );
	$profile_card_item['c_lazy_image']['c_lazy_image_alt_attr']        = \PMC::get_attachment_image_alt_text( $thumbnail_id );
	$profile_card_item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$profile_card_item['c_lazy_image']['c_lazy_image_link_url']        = get_permalink();

	$profile_card_array[] = $profile_card_item;
}

$profile_card_list['profile_card_list'] = $profile_card_array;

\PMC::render_template(
	sprintf( '%s/build/patterns/modules/profile-card-list.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$profile_card_list,
	true
);
