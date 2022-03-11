<?php
/**
 * Featured Image
 *
 * Only in use on the featured article.
 *
 * Copied from pmc-artnews-2019
 */

$variant = ! empty( $variant ) ? $variant : 'prototype';

$featured_image = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/featured-image.' . $variant );

$is_featured_article = \Variety\Inc\Featured_Article::get_instance()->is_featured_article();

$image_size = $is_featured_article ? 'landscape-xxxlarge' : 'landscape-large';

$data = \PMC\Core\Inc\Media::get_instance()->get_image_data_by_post( get_the_ID(), $image_size );

// TODO: These values should come in get_image_data_by_post
$image_id             = get_post_thumbnail_id( get_the_ID() );
$data['image_width']  = wp_get_attachment_metadata( $image_id )['width'];
$data['image_height'] = wp_get_attachment_metadata( $image_id )['height'];

if ( empty( $data['src'] ) ) {
	return;
}

$featured_image['o_figure']['c_lazy_image']['c_lazy_image_alt_attr']        = $data['image_alt'];
$featured_image['o_figure']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
$featured_image['o_figure']['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( get_post_thumbnail_id(), $image_size );
$featured_image['o_figure']['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( get_post_thumbnail_id(), $image_size );
$featured_image['o_figure']['c_lazy_image']['c_lazy_image_src_url']         = $data['src'];
$featured_image['o_figure']['c_lazy_image']['c_lazy_image_width_attr']      = $data['image_width'];
$featured_image['o_figure']['c_lazy_image']['c_lazy_image_height_attr']     = $data['image_height'];
$featured_image['o_figure']['c_figcaption']['c_figcaption_caption_markup']  = $data['image_caption'];
$featured_image['o_figure']['c_figcaption']['c_figcaption_credit_text']     = $data['image_credit'];

// We need to get image_height and image_width form \PMC\Core\Inc\Media::get_instance()->get_image_data_by_post
if ( ! empty( $data['image_height'] ) && ! empty( $data['image_width'] ) ) {
	$featured_image['o_figure']['c_lazy_image']['c_lazy_image_crop_style_attr'] = 'padding-bottom:calc((' . $data['image_height'] . '/' . $data['image_width'] . ')*100%);';
}

\PMC::render_template(
	sprintf(
		'%s/template-parts/patterns/modules/featured-image.php',
		untrailingslashit( CHILD_THEME_PATH )
	),
	$featured_image,
	true
);

//EOF
