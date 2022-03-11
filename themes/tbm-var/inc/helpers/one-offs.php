<?php
/**
 * One off functions for specific cases.
 * These are mostly used in templates but instead of cluttering templates
 * with logic, the logical operation is done here in one off functions
 * to keep the templates clutter free and straightforward.
 *
 * IMPORTANT: Multi-use/re-usable functions should not be placed in this file.
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-09-05
 *
 * @package pmc-variety-2017
 */


/**
 * Method to get playlist videos for single video page.
 *
 * @param object $current_video Current video post object
 * @param int    $playlist_id   Term ID for the playlist from which videos are needed
 * @param int    $count         Number of videos to add in playlist
 *
 * @return array
 */
function variety_get_playlist_videos_for_single_page( object $current_video, int $playlist_id = 0, int $count = 7, bool $should_backfill = true ) : array {

	$videos = [];

	if ( empty( $current_video ) || 1 > $count ) {
		return $videos;
	}

	$video = \Variety\Inc\Video::get_instance();

	if ( 1 > $playlist_id ) {
		$playlists   = get_the_terms( $current_video->ID, $video->tax );
		$playlist_id = ( ! empty( $playlists[0]->term_id ) ) ? intval( $playlists[0]->term_id ) : 0;
	}

	if ( 1 > $playlist_id ) {
		return $videos;
	}

	$rest   = $video->get_taxonomy_vcat_data( 1, 0, $playlist_id, true, ( $count + 1 ) );  // get one extra in-case current video is also returned
	$videos = ( ! empty( $rest[0]['posts'] ) ) ? $rest[0]['posts'] : [];

	if ( ! empty( $videos ) ) {

		foreach ( $videos as $k => $v ) {
			if ( $current_video->ID === $v->ID ) {
				unset( $videos[ $k ] );
				break;
			}
		}

		$videos = array_values( (array) $videos );

	}

	$videos = array_merge( [ $current_video ], $videos );
	$videos = array_slice( (array) $videos, 0, $count );

	if ( $count > count( $videos ) && true === $should_backfill ) {

		$args  = [
			'post_type'        => \Variety_Top_Videos::POST_TYPE_NAME,
			'posts_per_page'   => ( $count * 2 ),
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_status'      => 'publish',
			'suppress_filters' => false,
		];
		$posts = get_posts( $args );    // phpcs:ignore

		if ( ! empty( $posts ) && ! is_wp_error( $posts ) ) {

			$ids_to_exclude = wp_list_pluck( $videos, 'ID' );

			$posts = array_values(
				array_filter(
					$posts,
					function( \WP_Post $p ) use ( $ids_to_exclude ) : bool {

						if ( ! in_array( $p->ID, (array) $ids_to_exclude, true ) ) {
							return true;
						}

						return false;

					}
				)
			);

			$posts  = array_slice( (array) $posts, 0, ( $count - count( $videos ) ) );
			$videos = array_merge( (array) $videos, (array) $posts );

		}

	}

	return $videos;

}

//EOF
