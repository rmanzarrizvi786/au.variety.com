<?php
/**
 * Top Stories VIP Template.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;
use Variety\Plugins\Variety_VIP\Special_Reports;
use Variety\Inc\Carousels;

$data   = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/top-stories-carousel.variety-vip' );
$_posts = \Variety\Plugins\Variety_VIP\Carousel::get_vip_carousel_posts( 'vip-top-stories', 3, false, [ Content::VIP_POST_TYPE, Special_Reports::POST_TYPE ] );

if ( empty( $_posts ) ) {
	return;
}

$subscribe_url                    = '/vip-subscribe/?utm_source=site&utm_medium=VIP_NonSub&utm_campaign=VIPShop';
$data['c_title']['c_title_url']   = $subscribe_url;
$data['c_button']['c_button_url'] = $subscribe_url;

$template = $data['top_stories_carousel'][0];

$data['top_stories_carousel'] = [];

foreach ( $_posts as $index => $_post ) {

	$item = $template;

	$item['o_top_story_classes'] .= ' o-top-story // a-crop-375x475@mobile-max a-crop-923x539 lrv-u-width-100p u-height-375 js-Flickity-cell';

	$item['c_title']['c_title_text'] = variety_get_card_title( $_post );
	$item['c_title']['c_title_url']  = get_permalink( $_post );

	if ( ! empty( $_post->custom_excerpt ) ) {
		$item['c_dek']['c_dek_text'] = wp_strip_all_tags( $_post->custom_excerpt );
	} else {
		$item['c_dek']['c_dek_text'] = \PMC\Core\Inc\Helper::get_the_excerpt( $_post->ID );
	}

	if ( ! empty( $_post->image_id ) ) {
		$thumbnail = $_post->image_id;
	} else {
		$thumbnail = get_post_thumbnail_id( $_post );
	}

	$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

	if ( ! empty( $thumbnail ) ) {

		$item['c_lazy_image']['c_lazy_image_link_url']        = get_permalink( $_post );
		$item['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
		$item['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
		$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$item['c_lazy_image']['c_lazy_image_srcset_attr']     = wp_get_attachment_image_srcset( $thumbnail );

	} else {

		$item['c_lazy_image']['c_lazy_image_link_url']        = '';
		$item['c_lazy_image']['c_lazy_image_alt_attr']        = '';
		$item['c_lazy_image']['c_lazy_image_src_url']         = '';
		$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$item['c_lazy_image']['c_lazy_image_srcset_attr']     = '';

	}

	$category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $_post->ID, Content::VIP_CATEGORY_TAXONOMY );

	if ( ! empty( $category ) ) {
		$item['o_indicator']['c_span']['c_span_text'] = $category->name;
		$item['o_indicator']['c_span']['c_span_url']  = get_term_link( $category );
	}

	$data['top_stories_carousel'][] = $item;

	if ( ! empty( $item_2 ) ) {

		$item_2['c_title']['c_title_text']                      = $item['c_title']['c_title_text'];
		$item_2['c_title']['c_title_url']                       = $item['c_title']['c_title_url'];
		$item_2['c_lazy_image']['c_lazy_image_link_url']        = $item['c_lazy_image']['c_lazy_image_link_url'];
		$item_2['c_lazy_image']['c_lazy_image_alt_attr']        = $item['c_lazy_image']['c_lazy_image_alt_attr'];
		$item_2['c_lazy_image']['c_lazy_image_src_url']         = $item['c_lazy_image']['c_lazy_image_src_url'];
		$item_2['c_lazy_image']['c_lazy_image_placeholder_url'] = $item['c_lazy_image']['c_lazy_image_placeholder_url'];
		$item_2['c_lazy_image']['c_lazy_image_srcset_attr']     = $item['c_lazy_image']['c_lazy_image_srcset_attr'];
		$item_2['c_span']['c_span_text']                        = $item['o_indicator']['c_span']['c_span_text'];
		$item_2['c_span']['c_span_url']                         = $item['o_indicator']['c_span']['c_span_url'];

		$data['o_tease_list']['o_tease_list_items'][] = $item_2;

	}
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/top-stories-carousel.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
