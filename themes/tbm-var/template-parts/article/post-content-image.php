<?php
/**
 * Post Content Image.
 *
 * Initially brought in from the Artnews theme, and should be moved to
 * pmc-core-v2. Data is handled in class-image-captions.php.
 */

$post_content_image['post_content_image_classes']                             .= $figure_classes;
$post_content_image['o_figure']['c_figcaption']['c_figcaption_caption_markup'] = $image_caption;
$post_content_image['o_figure']['c_figcaption']['c_figcaption_credit_text']    = $image_credit;

if ( ! empty( $image_link ) ) {
	$post_content_image['o_figure']['c_lazy_image']['c_lazy_image_link_url'] = $image_link;
}

if ( ! empty( $shortcode_width ) ) {
	$post_content_image['o_figure']['o_figure_width_attr'] = sprintf( 'width:%1$spx', $shortcode_width );
}

if ( \PMC::is_amp() ) {
	$post_content_image['o_figure']['c_lazy_image']['c_lazy_image_markup']     = $image_markup;
	$post_content_image['o_figure']['c_lazy_image']['c_lazy_image_crop_class'] = false;
} else {
	$post_content_image['o_figure']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$post_content_image['o_figure']['c_lazy_image']['c_lazy_image_src_url']         = $image_src;
	$post_content_image['o_figure']['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $image_id );
	$post_content_image['o_figure']['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $image_id );
	$post_content_image['o_figure']['c_lazy_image']['c_lazy_image_height_attr']     = $image_height;
	$post_content_image['o_figure']['c_lazy_image']['c_lazy_image_width_attr']      = $image_width;

	if ( ! empty( $image_height ) && ! empty( $image_width ) ) {
		$post_content_image['o_figure']['c_lazy_image']['c_lazy_image_crop_style_attr'] = 'padding-bottom:calc((' . $image_height . '/' . $image_width . ')*100%);';
	}
}

\PMC::render_template(
	PMC_CORE_PATH . '/template-parts/patterns/modules/post-content-image.php',
	$post_content_image,
	true
);
