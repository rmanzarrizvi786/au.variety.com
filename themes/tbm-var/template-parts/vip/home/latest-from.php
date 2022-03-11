<?php
/**
 * Latest From VIP Template.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;
use Variety\Inc\Carousels;

$data   = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/latest-from.homepage.variety-vip' );
$_posts = \Variety\Plugins\Variety_VIP\Carousel::get_vip_carousel_posts( 'vip-latest-stories', 3 );

if ( empty( $_posts ) ) {
	return;
}

$count = count( $_posts );

$template_primary                           = $data['o_tease_primary'];
$template_item                              = $data['o_tease_list']['o_tease_list_items'][0];
$template_item_last                         = $data['o_tease_list']['o_tease_list_items'][1];
$data['o_tease_primary']                    = [];
$data['o_tease_list']['o_tease_list_items'] = [];

foreach ( $_posts as $index => $_post ) {
	if ( 0 === $index ) {
		$item = $template_primary;
	} elseif ( $count === $index ) {
		$item = $template_item_last;
	} else {
		$item = $template_item;
	}

	$item['c_title']['c_title_text'] = variety_get_card_title( $_post );
	$item['c_title']['c_title_url']  = get_permalink( $_post );

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
		$item['c_span']['c_span_text'] = $category->name;
		$item['c_span']['c_span_url']  = get_term_link( $category );
	}

	$author = \PMC\Core\Inc\Author::get_instance()->authors_data( $_post->ID );

	if ( ! empty( $author['byline'] ) ) {
		$item['c_link']['c_link_text'] = wp_strip_all_tags( sprintf( 'By %1$s', $author['byline'] ) );

		if ( ! empty( $author['single_author'] ) ) {
			$item['c_link']['c_link_url'] = get_author_posts_url( $author['single_author']['author']->ID, $author['single_author']['author']->user_nicename );
		}
	} else {
		$item['c_link']['c_link_text'] = '';
		$item['c_link']['c_link_url']  = '';
	}

	$item['c_timestamp']['c_timestamp_text'] = variety_human_time_diff( $_post->ID );

	if ( 0 === $index ) {
		$data['o_tease_primary'] = $item;
	} else {
		$data['o_tease_list']['o_tease_list_items'][] = $item;
	}
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/latest-from.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
