<?php
/**
 * Configuration for pmc-post-listing-filters plugin.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-04-27 READS-1155
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Post_Listing_Filters {

	use Singleton;

	/**
	 * Class Constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Add action and filters hooks.
	 */
	protected function _setup_hooks() {
		/**
		 * Filters.
		 */
		add_filter( 'pmc_post_listing_filters_taxonomies', array( $this, 'filter_pmc_post_listing_filters_taxonomies' ) );
		add_filter( 'pmc_post_listing_filters_post_types', array( $this, 'filter_pmc_post_listing_filters_post_types' ) );
	}

	/**
	 * Filter taxonomies in post filtering module.
	 *
	 * @param array $taxonomies array of taxonomies.
	 *
	 * @return array $taxonomies
	 */
	public function filter_pmc_post_listing_filters_taxonomies( $taxonomies ) {

		if ( ! is_array( $taxonomies ) ) {
			$taxonomies = array();
		}

		$taxonomies[] = '_post-options';

		return $taxonomies;
	}

	/**
	 * Filter post types in post filtering module.
	 *
	 * @param array $post_types array of post types.
	 *
	 * @return array $post_types
	 */
	public function filter_pmc_post_listing_filters_post_types( $post_types ) {

		if ( ! is_array( $post_types ) ) {
			$post_types = array();
		}

		$post_types[] = 'pmc-gallery';
		$post_types[] = 'pmc_list';

		return $post_types;
	}
}
