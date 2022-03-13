<?php

$profile_gallery = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-gallery.prototype' );

if ( ! PMC::has_linked_gallery( get_the_ID() ) ) {
	return;
}

$linked_gallery = \PMC\Gallery\View::get_linked_gallery_data( get_the_ID() );

if ( empty( $linked_gallery ) ) {
	return;
}

$profile_gallery['c_heading']['c_heading_text'] = esc_html__( 'Explore Their Collection', 'pmc-profiles' );

$images                   = get_post_meta( $linked_gallery['id'], \PMC\Gallery\Defaults::NAME, true ) ?: [];
$linked_gallery['images'] = array_values( $images );

$gallery_item_array = [];

$gallery_item_prototype = $profile_gallery['galleries'][0];

foreach ( $linked_gallery['images'] as $image_id ) {

	$gallery_item = $gallery_item_prototype;

	$gallery_item['c_lazy_image']['c_lazy_image_src_url']         = wp_get_attachment_image_url( $image_id, 'large', false );
	$gallery_item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$gallery_item['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $image_id );
	$gallery_item['c_lazy_image']['c_lazy_image_alt_attr']        = \PMC::get_attachment_image_alt_text( $image_id );
	$gallery_item['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $image_id );

	$gallery_item['c_figcaption']['c_figcaption_credit_text']    = \PMC\Core\Inc\Media::get_instance()->get_photo_credit( $image_id );
	$gallery_item['c_figcaption']['c_figcaption_caption_markup'] = wp_get_attachment_caption( $image_id );

	$gallery_item_array[] = $gallery_item;
}

$profile_gallery['galleries'] = $gallery_item_array;

\PMC::render_template(
	sprintf( '%s/build/patterns/modules/profile-gallery.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$profile_gallery,
	true
);
