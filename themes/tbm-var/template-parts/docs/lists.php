<?php
/**
 * Lists Template.
 *
 * @package pmc-variety-2020
 */

// Get default docs stories row data.
$lists = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/docs-stories-row.prototype' );

$lists_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-docs-lists',
	5,
);

if ( empty( $lists_data ) || count( $lists_data ) < 5 ) {
	return;
}

$lists['c_span']['c_span_text']                = 'Lists';
$lists['large_story']['c_span']['c_span_text'] = 'Lists';

$o_card_prototype  = $lists['stories_row_items'][0];
$stories_row_items = [];

foreach ( $lists_data as $key => $list ) {
	// If first review, set to large top story.
	if ( array_key_first( $lists_data ) === $key ) {
		$lists['large_story']['c_title']['c_title_text'] = $list->post_title;
		$lists['large_story']['c_title']['c_title_url']  = isset( $list->url ) ? $list->url : get_the_permalink( $list );

		$lists['large_story']['c_dek']['c_dek_text'] = isset( $list->custom_excerpt ) ? $list->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $list->ID ) );

		$author_data = PMC\Core\Inc\Author::get_instance()->authors_data( $list->ID );
		$author_url  = get_author_posts_url( $author_data['single_author']['author']->ID, $author_data['single_author']['author']->user_nicename );

		$lists['large_story']['c_link']['c_link_text'] = "By {$author_data['single_author']['author']->display_name}";
		$lists['large_story']['c_link']['c_link_url']  = $author_url;

		$image_id = isset( $list->image_id ) ? $list->image_id : get_post_thumbnail_id( $list->ID );

		$lists['large_story']['c_lazy_image']['c_lazy_image_link_url']           = isset( $list->url ) ? $list->url : get_the_permalink( $list );
		$lists['large_story']['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$lists['large_story']['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'landscape-xlarge' );
		$lists['large_story']['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$lists['large_story']['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$lists['large_story']['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
		$lists['large_story']['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

		continue;
	}

	// Otherwise, set to bottom row card.
	$o_card = $o_card_prototype;

	$o_card['c_title']['c_title_text'] = $list->post_title;
	$o_card['c_title']['c_title_url']  = isset( $list->url ) ? $list->url : get_the_permalink( $list );

	$o_card['c_dek']['c_dek_text'] = isset( $list->custom_excerpt ) ? $list->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $list->ID ) );

	$image_id = isset( $list->image_id ) ? $list->image_id : get_post_thumbnail_id( $list->ID );

	$o_card['c_lazy_image']['c_lazy_image_link_url']           = isset( $list->url ) ? $list->url : get_the_permalink( $list );
	$o_card['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$o_card['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'landscape-xlarge' );
	$o_card['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$o_card['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$o_card['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
	$o_card['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

	array_push( $stories_row_items, $o_card );
}

$lists['stories_row_items'] = $stories_row_items;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/docs-stories-row.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$lists,
	true
);
