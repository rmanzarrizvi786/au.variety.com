<?php
/**
 * This file contains the PMC\RollingStone\Mobile_API\Endpoints\Latest_lists class
 *
 * @package RS_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use WP_Query;

/**
 * Latest Lists endpoint class.
 */
class Latest_Lists extends Latest_News {

	/**
	 * Get river.
	 *
	 * @param \WP_REST_Request $request REST request data.
	 * @return array
	 */
	protected function get_river( $request ): array {
		$query = new WP_Query(
			[
				'post_type'      => 'pmc_list',
				'fields'         => 'ids',
				'order'          => 'DESC',
				'post_status'    => 'publish',
				'paged'          => $request['page'],
				'posts_per_page' => $request['per_page'],
			]
		);

		// Check if there are any posts.
		if ( empty( $query->posts ) || ! is_array( $query->posts ) ) {
			return [ 'items' => [] ];
		}

		// Assign total posts for pagination.
		$this->total_items = $query->found_posts;

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
