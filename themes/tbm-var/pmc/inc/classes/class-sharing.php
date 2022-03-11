<?php
/**
 * Sharing
 *
 * Handles Social Sharing from the pmc-social-bar plugin.
 *
 * The parent Class is found in pmc-plugins/pmc-social-bar/classes/frontend.php.
 *
 * @see pmc-plugins/pmc-social-bar/classes/frontend.php
 * @package pmc-core-v2
 */

namespace PMC\Core\Inc;

use \PMC\Social_Share_Bar;

/**
 * Class Sharing
 *
 * Handler for Sharing icon display.
 *
 * @see Social_Share_Bar\Frontend
 */
class Sharing extends Social_Share_Bar\Frontend {

	/**
	 * Get Icons
	 *
	 * Fetches the social icons arrays from the settings.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array An array of social sharing icons.
	 */
	public function get_icons( $post_id = 0 ) {

		global $post;

		if ( ! empty( $post_id ) ) {
			// Overriding to get icons based on post_id.
			$post = get_post( $post_id ); // WPCS: override ok.
		}

		$icons = [
			'primary'   => $this->get_icons_from_cache( Social_Share_Bar\Admin::PRIMARY, $post->post_type ),
			'secondary' => $this->get_icons_from_cache( Social_Share_Bar\Admin::SECONDARY, $post->post_type ),
		];

		wp_reset_postdata();

		return $icons;
	}

	/**
	 * Has Icons
	 *
	 * Checks that proper array keys are set as arrays, and that they are not empty.
	 *
	 * @param array $icons An array of icons.
	 * @return bool If the proper array keys are valid.
	 */
	public static function has_icons( $icons = array() ) {
		return (
			! empty( $icons['primary'] ) &&
			! empty( $icons['secondary'] ) &&
			is_array( $icons['primary'] ) &&
			is_array( $icons['secondary'] )
		);
	}

}
