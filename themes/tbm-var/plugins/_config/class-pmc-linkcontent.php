<?php
/**
 * PMC LinkContent configuration
 *
 * @package pmc-variety-2017
 *
 * @since 2017.1.0
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class PMC_LinkContent
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class PMC_LinkContent {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Initialize actions and filters.
	 */
	protected function _setup_hooks() {

		add_filter( 'pmclinkcontent_post_types', array( $this, 'set_post_types' ) );

	}

	/**
	 * Sets PMC link content post type.
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public function set_post_types( $post_types ) {

		return array_merge( $post_types, array( \Variety_Top_Videos::POST_TYPE_NAME ) );

	}
}
