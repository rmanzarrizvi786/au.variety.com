<?php
/**
 * More From VIP Template.
 *
 * @package pmc-variety
 */

use \Variety\Plugins\Variety_VIP\Content;

$more_from = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/more-from-widget.variety-vip' );

if ( ! empty( $more_from_widget_classes ) ) {
	$more_from['more_from_widget_classes'] = $more_from_widget_classes;
}

if ( ! empty( $o_tease_list_classes ) ) {
	$more_from['o_tease_list']['o_tease_list_classes'] = $o_tease_list_classes;
}

$more_from['o_more_link']['c_link']['c_link_url'] = '/vip/page/2/';

$latest     = Content::get_latest_posts( Content::VIP_POSTS_PER_PAGE_ON_HOMEPAGE );
$template_1 = $more_from['o_tease_list']['o_tease_list_items'][0];
$template_2 = $more_from['o_tease_list']['o_tease_list_items'][1];

$more_from['o_tease_list']['o_tease_list_items'] = [];

if ( ! empty( $latest->posts ) ) {
	foreach ( $latest->posts as $index => $_post ) {
		if ( 0 === $index ) {
			$item = $template_1;
		} else {
			$item = $template_2;
		}

		$item['c_title']['c_title_text'] = pmc_get_title( $_post );
		$item['c_title']['c_title_url']  = get_permalink( $_post );

		$thumbnail = get_post_thumbnail_id( $_post );

		if ( ! empty( $thumbnail ) ) {

			$item['c_lazy_image']['c_lazy_image_link_url']        = get_permalink( $_post );
			$item['c_lazy_image']['c_lazy_image_alt_attr']        = get_post_meta( $thumbnail, '_wp_attachment_image_alt', true );
			$item['c_lazy_image']['c_lazy_image_src_url']         = get_the_post_thumbnail_url( $_post );
			$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$item['c_lazy_image']['c_lazy_image_srcset_attr']     = wp_get_attachment_image_srcset( $thumbnail );

		} else {

			$item['c_lazy_image']['c_lazy_image_link_url']        = '';
			$item['c_lazy_image']['c_lazy_image_alt_attr']        = '';
			$item['c_lazy_image']['c_lazy_image_src_url']         = '';
			$item['c_lazy_image']['c_lazy_image_placeholder_url'] = '';
			$item['c_lazy_image']['c_lazy_image_srcset_attr']     = '';

		}

		$item['c_timestamp']['c_timestamp_text'] = variety_human_time_diff( $_post->ID );

		$category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $_post->ID, Content::VIP_CATEGORY_TAXONOMY );

		if ( ! empty( $category ) ) {
			$item['c_link']['c_link_text'] = $category->name;
			$item['c_link']['c_link_url']  = get_term_link( $category );
		}

		$more_from['o_tease_list']['o_tease_list_items'][] = $item;
	}
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/more-from-widget.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$more_from,
	true
);
