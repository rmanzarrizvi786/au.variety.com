<?php
/**
 * This file contains the PMC\VY\Mobile_API\Endpoints\VY_Latest_News class
 *
 * @package VY_Mobile_API
 */

namespace PMC\VY\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Latest_News;
use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use WP_Query;

/**
 * Latest News endpoint class.
 */
class VY_Latest_News extends Latest_News {

	/**
	 * Get river.
	 *
	 * @param \WP_REST_Request $request REST request data.
	 *
	 * @return array
	 */
	protected function get_river( $request ): array {
		$query = new WP_Query(
			[
				'post_type'      => [ 'post', 'pmc-gallery', 'pmc_list', 'pmc_top_video' ],
				'fields'         => 'ids',
				'order'          => 'DESC',
				'post_status'    => 'publish',
				'paged'          => $request['page'],
				'posts_per_page' => 8,
			]
		);

		$vip_query = new WP_Query(
			[
				'post_type'      => [ 'variety_vip_post', 'variety_vip_video' ],
				'fields'         => 'ids',
				'order'          => 'DESC',
				'post_status'    => 'publish',
				'paged'          => $request['page'],
				'posts_per_page' => 2,
			]
		);

		// Check if there are any posts.
		if ( empty( $query->posts ) || ! is_array( $query->posts ) ) {
			return [ 'items' => [] ];
		}

		$latest_posts = [];
		$vip_insert   = 0;

		foreach ( $query->posts as $key => $value ) {
			$latest_posts[] = $value;
			if ( ( $key + 1 ) % 4 === 0 ) {
				$latest_posts[] = $vip_query->posts[ $vip_insert ];
				$vip_insert ++;
			}
		}

		// Assign total posts for pagination.
		$this->total_items = intval( $query->found_posts ) + intval( $vip_query->found_posts );

		return [
			'items' => array_map(
				function( $post_id ) {
					return ( new Article_Object( \get_post( $post_id ) ) )->get_post_card();
				},
				(array) $latest_posts
			),
		];
	}
}
