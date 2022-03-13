<?php

use PMC\Core\Inc\Helper;

$profile_related_stories = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-related-stories.prototype' );

$terms = get_the_terms( get_the_ID(), 'post_tag' );

if ( is_wp_error( $terms ) || false === $terms ) {
	return;
}

$term_posts = [];

foreach ( $terms as $term_item ) {

	$term_id    = $term_item->term_id ?? '';
	$term_posts = array_merge( $term_posts, Helper::get_term_posts_cache( $term_id, 'post_tag', 3 ) );

	if ( count( $term_posts ) >= 3 ) {
		break;
	}
}

if ( empty( $term_posts ) ) {
	return;
}

$card_list_item_prototype = $profile_related_stories['o_card_list']['o_card_list_items'][0];

$card_list_item_array = [];


foreach ( array_slice( $term_posts, 0, 3 ) as $term_post ) {
	$post_item = $card_list_item_prototype;

	$post_item['c_title']['c_title_text'] = $term_post->post_title;
	$post_item['c_title']['c_title_url']  = get_permalink( $term_post->ID );

	$thumbnail_id = get_post_thumbnail_id( $term_post->ID );

	$primary_category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $term_post->ID, 'category' );

	$post_item['c_span']['c_span_text'] = $primary_category->name ?? '';
	$post_item['c_span']['c_span_url']  = get_tag_link( $primary_category->term_id ?? '' );

	$post_item['c_lazy_image']['c_lazy_image_src_url']         = wp_get_attachment_image_url( $thumbnail_id, 'medium', false );
	$post_item['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $thumbnail_id );
	$post_item['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $thumbnail_id );
	$post_item['c_lazy_image']['c_lazy_image_alt_attr']        = \PMC::get_attachment_image_alt_text( $thumbnail_id );
	$post_item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$post_item['c_lazy_image']['c_lazy_image_link_url']        = get_permalink( $term_post->ID );

	$card_list_item_array[] = $post_item;
}

$profile_related_stories['o_card_list']['o_card_list_items'] = $card_list_item_array;


\PMC::render_template(
	sprintf( '%s/build/patterns/modules/profile-related-stories.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$profile_related_stories,
	true
);
