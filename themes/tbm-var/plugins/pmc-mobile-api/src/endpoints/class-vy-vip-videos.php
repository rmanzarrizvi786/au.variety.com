<?php
/**
 * This file contains the PMC\VY\Mobile_API\Endpoints\VY_VIP_Videos class
 *
 * @package VY_VIP_Videos
 */

namespace PMC\VY\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Latest_News;
use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use WP_Query;

/**
 * VIP Videos endpoint class.
 */
class VY_VIP_Videos extends Latest_News {

	/**
	 * Get river.
	 *
	 * @param \WP_REST_Request $request REST request data.
	 *
	 * @return array
	 */
	protected function get_river( \WP_REST_Request $request ): array {

		$query = new WP_Query(
			[
				'post_type'      => [ 'variety_vip_video' ],
				'fields'         => 'ids',
				'order'          => 'DESC',
				'post_status'    => 'publish',
				'paged'          => $request['page'],
				'posts_per_page' => 10,
			]
		);

		// Check if there are any posts.
		if ( empty( $query->posts ) || ! is_array( $query->posts ) ) {
			return [ 'items' => [] ];
		}

		// Assign total posts for pagination.
		$this->total_items = $query->post_count;

		return [
			'items' => array_map(
				function( $post_id ) {
					return ( new Article_Object( \get_post( $post_id ) ) )->get_post_card();
				},
				(array) $query->posts
			),
		];
	}
}
