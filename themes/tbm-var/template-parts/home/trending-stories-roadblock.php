<?php
/**
 * Homepage Trending Stories Roadblock
 *
 * @package pmc-variety
 */

$trending_stories_roadblock = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/trending-stories-roadblock.prototype' );

$days   = 7;
$period = 1;

if ( ! \PMC::is_production() ) {
	$days   = 300;
	$period = 300;
}
$trending = \PMC\Core\Inc\Top_Posts::get_posts( 10, $days, $period, 'most_viewed' );

if ( empty( $trending ) || ! is_array( $trending ) ) {
	return;
}

$trending_story_prototype  = $trending_stories_roadblock['stories'][0];
$trending_story_item_array = [];

$trending_stories_roadblock['c_heading']['c_heading_text'] = esc_html__( 'Most-Read Stories', 'pmc-variety' );

foreach ( $trending as $trending_post ) {

	$trending_story_item = $trending_story_prototype;

	$image_data = \PMC\Core\Inc\Media::get_instance()->get_image_data_by_post( $trending_post['post_id'], 'variety-trending' );

	$trending_story_item['c_title']['c_title_markup'] = \PMC::truncate( $trending_post['post_title'], 110 );
	$trending_story_item['c_title']['c_title_url']    = $trending_post['post_permalink'];

	$trending_story_item['c_lazy_image']['c_lazy_image_src_url']            = $image_data['src'];
	$trending_story_item['c_lazy_image']['c_lazy_image_alt_attr']           = $image_data['image_alt'];
	$trending_story_item['c_lazy_image']['c_lazy_image_link_url']           = $trending_post['post_permalink'];
	$trending_story_item['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$trending_story_item['c_lazy_image']['c_lazy_image_screen_reader_text'] = $image_data['image_alt'];
	$trending_story_item['c_lazy_image']['c_lazy_image_srcset_attr']        = \wp_get_attachment_image_srcset( get_post_thumbnail_id( $trending_post['post_id'] ) );
	$trending_story_item['c_lazy_image']['c_lazy_image_sizes_attr']         = \wp_get_attachment_image_sizes( get_post_thumbnail_id( $trending_post['post_id'] ) );

	$trending_story_item_array[] = $trending_story_item;
}

$trending_stories_roadblock['stories'] = $trending_story_item_array;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/trending-stories-roadblock.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$trending_stories_roadblock,
	true
);
