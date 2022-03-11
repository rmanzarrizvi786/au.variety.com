<?php
/**
 * VIP Archive Template.
 *
 * @package pmc-variety
 */

use \Variety\Plugins\Variety_VIP\Content;

global $wp_query, $paged;

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/short-form-landing.prototype' );

if ( is_tax() ) {
	$current_term = get_queried_object();

	$data['more_from_widget']['o_more_from_heading']['c_heading']['c_heading_text']               = $current_term->name;
	$data['more_from_widget']['o_more_from_heading']['c_v_icon']                                  = false;
	$data['more_from_widget']['o_more_from_heading']['c_heading']['c_heading_is_primary_heading'] = true;
}

// Trending topics.
$_menu = wp_get_nav_menu_name( 'pmc_variety_vip_trending' );
$_menu = wp_get_nav_menu_object( $_menu );
$items = wp_get_nav_menu_items( $_menu->term_id );

if ( ! empty( $items ) ) {
	$template = $data['trending_topics']['trending_topics'][0];

	$data['trending_topics']['trending_topics'] = [];

	foreach ( $items as $item ) {
		if ( 'taxonomy' === $item->type && empty( $item->description ) ) {
			$description = wp_strip_all_tags( term_description( $item->object_id ) );
		} else {
			$description = $item->description;
		}

		$topic                                = $template;
		$topic['o_topic_url']                 = $item->url;
		$topic['c_heading']['c_heading_text'] = $item->title;
		$topic['c_tagline']['c_tagline_text'] = $description;

		$data['trending_topics']['trending_topics'][] = $topic;
	}
} else {
	$data['trending_topics']['trending_topics'] = [];
}

// Previous / Next.
$next_post_link = get_next_posts_link() ? next_posts( 0, false ) : false;
$prev_post_link = get_previous_posts_link() ? previous_posts( false ) : false;

// Next
if ( ! empty( $next_post_link ) ) {
	$data['more_from_widget']['o_more_link']['c_link']['c_link_url']  = $next_post_link;
	$data['more_from_widget']['o_more_link']['c_link']['c_link_text'] = __( 'More News', 'pmc-variety' );
} else {
	$data['more_from_widget']['o_more_link']['c_link'] = null;
}

// Previous.
if ( ! empty( $prev_post_link ) ) {
	$data['more_from_widget']['more_from_widget_is_paged']                     = true;
	$data['more_from_widget']['o_more_link_previous']['c_link']['c_link_url']  = $prev_post_link;
	$data['more_from_widget']['o_more_link_previous']['c_link']['c_link_text'] = __( 'Previous', 'pmc-variety' );
} else {
	// Hide link if there are no next posts
	$data['more_from_widget']['o_more_link_previous']['c_link'] = false;
}

$latest     = $wp_query->posts;
$template_1 = $data['more_from_widget']['o_tease_list']['o_tease_list_items'][0];
$template_2 = $data['more_from_widget']['o_tease_list']['o_tease_list_items'][1];

$data['more_from_widget']['o_tease_list']['o_tease_list_items'] = [];

if ( ! empty( $latest ) ) {
	foreach ( $latest as $index => $_post ) {
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
			$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$item['c_lazy_image']['c_lazy_image_srcset_attr']     = '';

		}

		$item['c_timestamp']['c_timestamp_text'] = variety_human_time_diff( $_post->ID );

		$category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $_post->ID, Content::VIP_CATEGORY_TAXONOMY );

		if ( ! empty( $category ) ) {
			$item['c_link']['c_link_text'] = $category->name;
			$item['c_link']['c_link_url']  = get_term_link( $category );
		}

		$data['more_from_widget']['o_tease_list']['o_tease_list_items'][] = $item;
	}
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/short-form-landing.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
