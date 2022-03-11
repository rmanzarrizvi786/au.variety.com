<?php
/**
 * Rewrites
 *
 * Setup rewrites for the theme.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Rewrites
 *
 * @since 2017.1.0
 * @see   \PMC\Global_Functions\Traits\Singleton
 */
class Rewrites {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Initializes the theme.
	 *
	 * @since 2017.1.0
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @since  2017-09-12 - Dhaval Parekh - CDWE-626
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Actions
		 */
		add_action( 'init', array( $this, 'on_action_init' ) );

		/**
		 * Filters
		 */

		/**
		 * Bind late,
		 * So, Can overwrite pmc-core's (parent theme) filter.
		 */
		add_filter( 'pmc_gallery_standalone_slug', array( $this, 'gallery_slug' ), 11 );

		// Premier page rules.
		add_filter( 'query_vars', array( $this, 'add_marketing_page_query_vars' ) );
	}

	/**
	 * Call functions on init action.
	 *
	 * @since  2017-09-16 - Dhaval Parekh - CDWE-626
	 *
	 * @return void
	 */
	public function on_action_init() {

		$this->_add_rewrite_rules();

	}

	/**
	 * Prioritise rewrite for 'access-digital' and other archive pages.
	 *
	 * This doesn't introduce an all new rewrite but prioritises an existing one which was
	 * being ignored in favour for the `category` urls.
	 *
	 * @since 2017.08.10
	 */
	protected function _add_rewrite_rules() {

		add_rewrite_rule( '([0-9]{4})/more/(.+?)/(.+-?)-(.+?)/amp(/(.*))?/?$', 'index.php?year=$matches[1]&category_name=$matches[2]&name=$matches[3]&amp=$matches[5]', 'top' );

		add_rewrite_rule( '([0-9]{4})/(.+?)/(.+?)/(.+-?)-(.+?)/amp(/(.*))?/?$', 'index.php?year=$matches[1]&vertical=$matches[2]&category_name=$matches[3]&name=$matches[4]&amp=$matches[6]', 'top' );

		// Rule for : 'year/vertical/category_name/post_slug-post_id/maz/'.
		add_rewrite_rule( '([0-9]{4})/(.+?)/(.+?)/(.+-?)-(.+?)/maz/?$', 'index.php?year=$matches[1]&vertical=$matches[2]&category_name=$matches[3]&name=$matches[4]&maz=1', 'top' );

		// Rule for : 'verical/category_name/page/page_number/'.
		add_rewrite_rule( '^v/([^/]+)/page/([0-9]+)/?$', 'index.php?vertical=$matches[1]&paged=$matches[2]', 'top' );

	}

	/**
	 * Query var for the marketing page to allow for different versions of the pricing grid.
	 *
	 * @param array $vars Query string param.
	 *
	 * @return array Query string param.
	 */
	function add_marketing_page_query_vars( $vars ) {
		$vars[] = 'location';

		return $vars;
	}

	/**
	 * Marketing page redirects for international versions with query var handling.
	 */
	/** TODO: move these to proper redirects
	 * function _marketing_page_rewrites() {
	add_rewrite_rule( 'subscribe-canada', 'index.php?pagename=subscribe-us&location=canada', 'top' );
	add_rewrite_rule( 'subscribe-international', 'index.php?pagename=subscribe-us&location=international', 'top' );
	}
	 */
	/**
	 * To get rewrite rule for pmc-gallery post typs.
	 *
	 * @since  2017-09-12 - Dhaval Parekh - CDWE-626
	 *
	 * @hook   pmc_gallery_standalone_slug
	 *
	 * @param  string $slug URL slug for gallery.
	 *
	 * @return string URL slug for gallery.
	 */
	public function gallery_slug( $slug ) {
		return 'gallery';
	}

}
