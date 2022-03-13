<?php
/**
 * To add evergreen content config in page meta for event tracking.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-05-25 READS-1155
 */

namespace PMC\Global_Functions;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Cache;
use WP_Post;

class Evergreen_Content {

	use Singleton;

	const SLUG        = 'evergreen-content';
	const CACHE_GROUP = 'evergreen_content';
	const CACHE_LIFE  = HOUR_IN_SECONDS * 12;

	/**
	 * Class Constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup filters and actions.
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {

		/**
		 * Actions.
		 */

		add_action( 'init', [ $this, 'add_default_term' ] );

		/**
		 * Filters.
		 */
		add_filter( 'pmc_post_options_custom_dims', [ $this, 'maybe_add_custom_dims' ] );
	}

	/**
	 * Add evergreen-content to custom dimension.
	 *
	 * @param array $slug_array
	 *
	 * @return array
	 */
	public function maybe_add_custom_dims( array $slug_array ) : array {
		$slug_array[] = self::SLUG;

		return $slug_array;
	}

	/**
	 * Method to add post option for Evergreen Content.
	 *
	 * @return void
	 */
	public function add_default_term() {

		// Checking Taxonomy class because it is loaded by post options plugin when plugin is loaded.
		// Autoloading is false to prevent PHP from loading class, make sure plugin does that job.
		if ( class_exists( '\PMC\Post_Options\Taxonomy', false ) ) {
			\PMC\Post_Options\API::get_instance()->register_global_options(
				[
					self::SLUG => [
						'label'       => 'Evergreen Content',
						'description' => 'Posts with this term will be set as Evergreen Content.',
					],
				]
			);

		}
	}
}
