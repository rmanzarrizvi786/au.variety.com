<?php
/**
 * This file contains the PMC\VY\Mobile_API\Endpoints\VY_Video_Landing class
 *
 * @package VY_Mobile_API
 */

namespace PMC\VY\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Home;
use Variety\Inc\Carousels;
use Variety\Inc\Video;

/**
 * Video Landing endpoint class.
 */
class VY_Video_Landing extends Home {

	/**
	 * Get Video Landing Page modules.
	 *
	 * @return array
	 */
	protected function get_modules(): array {

		$featured_articles = $this->get_featured_videos();
		$active_channels   = $this->get_active_channels();

		$video_modules = array_merge( $featured_articles, $active_channels );

		$sections = wp_cache_get( 'api:video:landing', 'pmc-mobile-api' );

		if ( false === $sections ) {

			$sections = $this->insert_ads( $video_modules, count( $video_modules ), true );

			wp_cache_set( 'api:video:landing', $sections, 'pmc-mobile-api', 30 * MINUTE_IN_SECONDS );

		}

		return $sections;
	}

	/**
	 * Insert ads at set interval.
	 *
	 * @param array $video_modules All widget data.
	 * @param int   $module_count  Count of home items.
	 * @param bool  $insert_ads    Insert ads if true.
	 *
	 * @return array
	 */
	public function insert_ads( $video_modules, $module_count, $insert_ads ): array {

		if ( ! $insert_ads ) {
			return $video_modules;
		}

		$output = [];
		$count  = 2;

		foreach ( $video_modules as $widget_data ) {
			$output[] = $widget_data;

			if ( 0 === $count % 2 && $count !== $module_count ) {
				$output[] = $this->get_ad();
			}

			$count ++;
		}

		return $output;
	}

	/**
	 * Get Featured Videos.
	 *
	 * @return array
	 */
	public function get_featured_videos() {

		$featured = Carousels::get_video_carousel_posts( 'featured-video', 8, false, 'post' );

		$featured_posts = wp_list_pluck( $featured, 'ID' );

		if ( empty( $featured ) ) {
			$featured = new \WP_Query(
				[
					'post_type'      => [ 'variety_top_video' ],
					'fields'         => 'ids',
					'order'          => 'DESC',
					'post_status'    => 'publish',
					'posts_per_page' => 8,
				]
			);

			$featured_posts = $featured->posts;
		}

		$items = array_map( [ $this, 'map_post_card' ], $featured_posts ?? [] );

		return [
			[
				'title'    => 'Featured Video',
				'template' => 'featured',
				'items'    => $items,
			],
		];
	}

	/**
	 * Get active channels.
	 *
	 * @return array
	 */
	public function get_active_channels() {
		$playlists = Video::get_instance()->get_video_archive_vcat_data( 'active_channels' );

		$playlists_modules = [];

		foreach ( $playlists as $playlist ) {
			$items = [];

			if ( ! empty( $playlist['posts'] ) ) {
				$post_ids = (array) wp_list_pluck( $playlist['posts'], 'ID' );
				$items    = array_map( [ $this, 'map_post_card' ], $post_ids ?? [] );
			}

			$playlists_modules[] = [
				'title'    => $playlist['heading'],
				'template' => 'default',
				'items'    => $items,
			];
		}

		return $playlists_modules;
	}
}
