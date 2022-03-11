<?php
/**
 * Popular Videos.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/popular-videos.prototype' );

//  Playlists.
$playlists = \Variety\Inc\Video::get_instance()->get_video_archive_vcat_data( $channel );

if ( empty( $playlists ) ) {
	return;
}

$template      = $data['popular_videos_items'][1];
$first_classes = $data['popular_videos_items'][0]['o_popular_videos_classes'];
$last_classes  = $data['popular_videos_items'][4]['o_popular_videos_classes'];

$data['popular_videos_items'] = [];

$count = 1;

foreach ( $playlists as $playlist ) {
	$item = $template;

	$item['c_heading']['c_heading_text']          = $playlist['heading'];
	$item['o_more_link']['c_link']['c_link_text'] = $playlist['more_text'];
	$item['o_more_link']['c_link']['c_link_url']  = $playlist['more_link'];

	if ( count( $playlists ) > 1 ) {
		if ( 1 === $count ) {
			$item['o_popular_videos_classes'] = $first_classes;
		} elseif ( 2 !== $count && count( $playlists ) === $count ) {
			$item['o_popular_videos_classes'] = $last_classes;
		}
	}

	if ( ! empty( $playlist['posts'] ) ) {
		// Featured.
		$featured                     = array_shift( $playlist['posts'] );
		$item['o_video_card_primary'] = \Variety\Inc\Video::get_instance()->populate_video_data( $item['o_video_card_primary'], $featured );

		// Grid.
		$template_1                     = $item['o_popular_videos_items'][0];
		$item['o_popular_videos_items'] = [];

		foreach ( $playlist['posts'] as $_post ) {
			$video = $template_1;
			$video = \Variety\Inc\Video::get_instance()->populate_video_data( $video, $_post );

			$item['o_popular_videos_items'][] = $video;
		}
	}

	$data['popular_videos_items'][] = $item;

	$count++;
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/popular-videos.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
