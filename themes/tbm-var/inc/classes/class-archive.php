<?php
/**
 * Archive
 *
 * Handles archive functionality.
 *
 * @package pmc-variety
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Archive
 *
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Archive {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Initializes the filters and actions.
	 */
	protected function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() {

		add_filter( 'template_include', [ $this, 'set_home_archive_template' ] );
		// Setting to higher priority against default post_type filter in Filter Posts plugin 
		add_action( 'pre_get_posts', [ $this, 'set_river_post_types' ], 12 );

	}

	/**
	 * Set Home Archive Template
	 *
	 * @param string $template Template name.
	 *
	 * @return string
	 */
	public function set_home_archive_template( $template ) {

		// Any page after the first on the homepage should show the archive template.
		if ( is_front_page() && is_paged() ) {

			return locate_template( 'index.php' );
		}

		return $template;
	}

	/**
	 * Adding post_types for homepage and archive page rivers.
	 *
	 * @param $query
	 */
	public function set_river_post_types( object $query ) : void {

		$taxonomies = [
			'hollywood_exec',
			'variety_vip_post',
			'variety_vip_report',
			'variety_vip_video',
			'variety_vip_category',
			'variety_top_video',
			'pmc_list',
			'pmc-gallery',
		];

		if ( ! is_admin() 
			&& $query->is_main_query() 
			&& ! is_singular() 
			&& ! is_post_type_archive( $taxonomies )
			&& ! is_tax( $taxonomies )
		) {

			$post_types = [ 'pmc_list', 'post' ];

			$existing_types = $query->get( 'post_type' );

			$existing_types = ( empty( $existing_types ) ) ? [] : (array) $existing_types;

			$post_types = array_merge( $existing_types, $post_types );

			$post_types = array_filter(
				array_unique(
					array_values( $post_types )
				)
			);

			$query->set( 'post_type', $post_types );

		}
	}
}
