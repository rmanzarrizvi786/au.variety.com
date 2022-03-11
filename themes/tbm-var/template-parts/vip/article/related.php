<?php
/**
 * Related Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/related-articles.variety-vip' );

if ( empty( $items ) || ! is_array( $items ) ) {
	return;
}

$template                 = $data['related_articles'][0];
$data['related_articles'] = [];
$count                    = 0;

foreach ( $items as $_post ) {
	if ( ! is_array( $_post ) ) {
		continue;
	}

	$item = $template;

	$item['c_heading']['c_heading_text'] = $_post['title'];
	$item['c_heading']['c_heading_url']  = $_post['url'];

	$thumbnail = get_post_thumbnail_id( $_post['id'] );
	$image     = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

	if ( 0 === $count && ! empty( $thumbnail ) ) {
		$item['c_lazy_image']['c_lazy_image_link_url']        = get_permalink( $_post['id'] );
		$item['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
		$item['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
		$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$item['c_lazy_image']['c_lazy_image_srcset_attr']     = wp_get_attachment_image_srcset( $thumbnail );
	} else {
		$item['c_lazy_image'] = false;
	}

	$data['related_articles'][] = $item;

	$count++;
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/related-articles.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
