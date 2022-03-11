<?php
/**
 * Author
 *
 * Responsible for author related functionality.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Author
 */
class Author {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() {

		add_filter( 'pmc_core_author_posts_post_types', [ $this, 'add_vip_post_type' ], 11 );

	}

	/**
	 * Add the VIP post type to the author query when fetching posts.
	 *
	 * @param array $post_types A list of custom post types for querying author posts.
	 *
	 * @return array
	 */
	public function add_vip_post_type( $post_types ) {

		if ( ! in_array( Content::VIP_POST_TYPE, (array) $post_types, true ) ) {
			$post_types[] = Content::VIP_POST_TYPE;
		}

		return $post_types;

	}

}

// EOF.
