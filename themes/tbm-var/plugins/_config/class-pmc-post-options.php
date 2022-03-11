<?php
/**
 * PMC Post Options config for Variety.
 *
 * @since   2020-02-19
 *
 * @package pmc-variety-2019
 */

namespace Variety\Plugins\Config;

use \PMC\Post_Options\API;
use \PMC\Global_Functions\Traits\Singleton;

class PMC_Post_Options {

	use Singleton;

	/**
	 * Initial function.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Function to setup actions/filters.
	 */
	protected function _setup_hooks() {

		// Actions.
		add_action( 'admin_init', array( $this, 'maybe_add_terms' ) );

		// Filters.
		add_filter(
			'pmc-post-options-allowed-types',
			array( $this, 'filter_pmc_post_options_allowed_types' ) ); // @codingStandardsIgnoreLine

	}

	/**
	 * Create post option.
	 *
	 * @return void
	 */
	public function maybe_add_terms() {

		API::get_instance()->register_options(
			array(
				'variety-featured-article' => [
					'label'    => 'Featured Article',
					'children' => [
						'variety-featured-article-vertical-image' => [
							'label'       => 'Vertical Header Image (Featured Article)',
							'description' => 'Show the featured image in portrait dimensions.',
						],
					],
				],
			)
		);

	}

	/**
	 * Filter post types in post options module.
	 *
	 * @param array $post_types array of post types.
	 *
	 * @return array $post_types
	 */
	public function filter_pmc_post_options_allowed_types( $post_types ) {

		if ( ! is_array( $post_types ) ) {
			$post_types = array();
		}

		$post_types[] = 'pmc-gallery';
		$post_types[] = 'pmc_list';
		$post_types[] = 'pmc_featured';

		return $post_types;
	}

}
