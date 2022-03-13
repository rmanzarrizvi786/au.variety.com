<?php
/**
 * REST controller for the Special Edition Articles post type.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily\REST_API;

use PMC\Digital_Daily\CPT;
use WP_Post;
use WP_REST_Posts_Controller;

/**
 * Class Special_Edition_Articles_Controller.
 */
class Special_Edition_Articles_Controller extends WP_REST_Posts_Controller {
	/**
	 * Restrict API requests to users that can edit Digital Daily post types.
	 *
	 * @param WP_Post $post Post object.
	 * @return bool
	 */
	public function check_read_permission( $post ): bool {
		if ( ! CPT::current_user_can( 'edit_posts' ) ) {
			return false;
		}

		return parent::check_read_permission( $post );
	}
}
