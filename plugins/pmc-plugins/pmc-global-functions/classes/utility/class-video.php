<?php
/**
 * Utilities for working with video-related posts and data.
 *
 * @package pmc-global-functions
 */

namespace PMC\Global_Functions\Utility;

use PMC_Featured_Video_Override;
use PMC\Top_Videos_V2\PMC_Top_Videos;

/**
 * Class Video.
 */
class Video {
	/**
	 * Retrieve data about a video associated with a post by one of several Core
	 * Tech plugins.
	 *
	 * @param int $post_id Post ID.
	 * @return array|null
	 */
	public static function get_player_data_from_post( int $post_id ): ?array {
		if ( empty( $post_id ) ) {
			return null;
		}

		$source = static::_get_player_data_from_source( $post_id );

		if ( empty( $source ) || ! is_string( $source ) ) {
			return null;
		}

		return static::_parse_source_player_data( $source );
	}

	/**
	 * Locate video data from any number of Core Tech plugins.
	 *
	 * @param int $post_id Post ID.
	 * @return string|null
	 */
	protected static function _get_player_data_from_source(
		int $post_id
	): ?string {
		$video_source = null;

		if (
			// Plugin: pmc-top-videos.
			class_exists( PMC_Top_Videos::class, false )
			&& PMC_Top_Videos::POST_TYPE_NAME === get_post_type( $post_id )
		) {
			$video_source = get_post_meta(
				$post_id,
				'pmc_top_video_source',
				true
			);

			if ( ! is_string( $video_source ) ) {
				$video_source = '';
			}
		} elseif (
			// Theme VY
			post_type_exists( 'variety_top_video' )
			&& 'variety_top_video' === get_post_type( $post_id )
		) {
			$video_source = get_post_meta(
				$post_id,
				'variety_top_video_source',
				true
			);

			if ( ! is_string( $video_source ) ) {
				$video_source = '';
			}
		} elseif (
			// Plugin: pmc-featured-video-override.
			class_exists(
				PMC_Featured_Video_Override::class,
				false
			)
		) {
			$meta = get_post_meta(
				$post_id,
				PMC_Featured_Video_Override::META_KEY,
				true
			);

			if ( empty( $meta ) ) {
				$meta = get_post_meta(
					$post_id,
					PMC_Featured_Video_Override::META_KEY_END,
					true
				);
			}

			if ( ! empty( $meta ) ) {
				$video_source = $meta;
				unset( $meta );
			}
		}

		return $video_source;
	}

	/**
	 * Extract and normalize video data.
	 *
	 * @param string $source Raw source data.
	 * @return array|null
	 */
	protected static function _parse_source_player_data(
		string $source
	): ?array {
		$data = [
			'id'     => null,
			'source' => null,
		];

		if (
			false !== stripos( $source, '[jwplayer' )
			|| false !== stripos( $source, '[jwplatform' )
		) {
			$found_id = preg_match(
				'#\[(jwplayer|jwplatform)[\s]+(?P<id>[a-z0-9]{8})-?(?P<player>[a-z0-9]{8})?[^\]]*\]#i',
				$source,
				$matches
			);

			if ( 1 === $found_id ) {
				$data['id']     = $matches['id'];
				$data['source'] = 'jwplayer';

				if ( ! empty( $matches['player'] ) ) {
					$data['player'] = $matches['player'];
				}
			}
		} elseif ( function_exists( 'jetpack_get_youtube_id' ) ) {
			$id = jetpack_get_youtube_id( $source );

			if ( ! empty( $id ) ) {
				$data['id']     = $id;
				$data['source'] = 'youtube';
			}
		}

		return ! empty( $data['id'] ) ? $data : null;
	}
}
