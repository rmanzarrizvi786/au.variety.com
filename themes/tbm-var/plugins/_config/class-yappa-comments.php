<?php
/**
 * Config class Yappa comments plugin.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class Yappa_Comments {

	use Singleton;

	/**
	 * Construct Method.
	 *
	 */
	public function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		// activate plugin
		add_filter( 'pmc_activate_yappa_plugin', '__return_true', 10 );
		add_filter( 'yappa_allowed_post_types', [ $this, 'get_allowed_post_types' ], 10, 1 );
		add_action( 'variety_render_comments', [ $this, 'render_widget' ] );
	}

	/**
	 * Set the post types that can have comments
	 *
	 * @param $post_types array
	 *
	 * @return array
	 */
	public function get_allowed_post_types( $post_types ): array {
		$post_types = array_merge(
			(array) $post_types,
			[
				'post',
				'pmc-gallery',
				'pmc_list',
				'variety_top_video',
			]
		);

		return array_unique( (array) $post_types );
	}

	/**
	 * Render the comments widget
	 */
	public function render_widget() {

		return \PMC\Yappa\Yappa::get_instance()->render_widget();
	}


}
//EOF
