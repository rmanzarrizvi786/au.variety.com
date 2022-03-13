<?php

use PMC\PMC_Profiles\PMC_Profiles;
use PMC\PMC_Profiles\Post_Type;

$profile_blurb = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-blurb.prototype' );

$profile_blurb['c_title']['c_title_text'] = get_the_title();

$secondary_taxonomy_slug  = Post_Type::get_instance()->get_taxonomy_slug( 'secondary' );
$secondary_taxonomy_terms = PMC_Profiles::get_term_list( get_the_ID(), $secondary_taxonomy_slug );

$profile_blurb['c_tagline']['c_tagline_text'] = ( ! empty( $secondary_taxonomy_terms ) ) ? implode( '; ', $secondary_taxonomy_terms ) : '';

$tertiary_taxonomy_slug   = Post_Type::get_instance()->get_taxonomy_slug( 'tertiary' );
$quaternary_taxonomy_slug = Post_Type::get_instance()->get_taxonomy_slug( 'quaternary' );

$tertiary_taxonomy_terms   = PMC_Profiles::get_term_list( get_the_ID(), $tertiary_taxonomy_slug );
$quaternary_taxonomy_terms = PMC_Profiles::get_term_list( get_the_ID(), $quaternary_taxonomy_slug );

$profile_blurb['c_tagline_second']['c_tagline_text'] = ( ! empty( $tertiary_taxonomy_terms ) ) ? implode( '; ', $tertiary_taxonomy_terms ) : '';

$profile_blurb['c_dek']['c_dek_markup'] = ( ! empty( $quaternary_taxonomy_terms ) ) ? implode( '; ', $quaternary_taxonomy_terms ) : '';
$profile_blurb['c_dek']['c_dek_text']   = false;

$thumbnail_id = get_post_thumbnail_id( get_the_ID() );

$profile_blurb['c_lazy_image']['c_lazy_image_src_url']         = wp_get_attachment_image_url( $thumbnail_id, 'medium', false );
$profile_blurb['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $thumbnail_id );
$profile_blurb['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $thumbnail_id );
$profile_blurb['c_lazy_image']['c_lazy_image_alt_attr']        = \PMC::get_attachment_image_alt_text( $thumbnail_id );
$profile_blurb['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();

$social_share_data = PMC\Core\Inc\Sharing::get_instance()->get_icons();

$primary_share_items = [];

$social_icon_prototype = $profile_blurb['o_social_list']['o_social_list_icons'][0];

if ( ! empty( $social_share_data['primary'] ) && is_array( $social_share_data['primary'] ) ) {

	foreach ( $social_share_data['primary'] as $icon_name => $icon_data ) {

		$share_icon = $social_icon_prototype;

		$share_icon['c_icon_url']      = $icon_data->url;
		$share_icon['c_icon_name']     = $icon_name;
		$share_icon['c_icon_rel_name'] = $icon_name;

		$primary_share_items[] = $share_icon;
	}
}

$profile_blurb['o_social_list']['o_social_list_icons'] = $primary_share_items;


\PMC::render_template(
	sprintf( '%s/build/patterns/modules/profile-blurb.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$profile_blurb,
	true
);
