<?php
/**
 * Setup for PMC Genre plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since 2015-03-27
 *
 * @version 2017-09-26 CDWE-677 Milind More
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Genre {

	use Singleton;

	protected $_post_types = array(
		'variety_top_video',
	);

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 *
	 * Initialize hooks.
	 */
	protected function _setup_hooks() {

		/*
		 * Filters
		 */
		add_filter( 'pmc-genre-post-types', [ $this, 'add_post_types' ] );
	}

	/**
	 * Override default tracking image for custom feeds
	 *
	 * @param array $post_types array of post types.
	 *
	 * @return array.
	 */
	public function add_post_types( $post_types = array() ) {
		if ( ! is_array( $post_types ) || empty( $this->_post_types ) ) {
			return $post_types;
		}

		$post_types = array_filter( array_unique( array_merge( $post_types, $this->_post_types ) ) );

		return $post_types;
	}

} //end of class

//EOF
