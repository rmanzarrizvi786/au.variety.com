<?php
/**
 * Rewrites
 *
 * Setup rewrites for the Variety VIP Plugin
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Rewrites
 *
 * @since 2017.1.0
 */
class Rewrites {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Initializes the theme.
	 *
	 * @since 2017.1.0
	 * @codeCoverageIgnore
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
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() {

		/**
		 * Actions
		 */
		add_action( 'init', [ $this, 'on_action_init' ], 1 );
		/**
		 * Filters
		 */

	}

	/**
	 * Call functions on init action.
	 *
	 * @since  2017-09-16 - Dhaval Parekh - CDWE-626
	 *
	 * @return void
	 * @codeCoverageIgnore
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
	 * @codeCoverageIgnore
	 */
	protected function _add_rewrite_rules() {

		// Rule for : 'vip/post_slug-post_id/'.
		add_rewrite_rule( '^vip/(.+-?)-(.+?)/?$', 'index.php?p=$matches[2]&post_type=variety_vip_post', 'top' );

		// Rule for : 'vip-special-reports/post_slug-post_id/'.
		add_rewrite_rule( '^vip-special-reports/(.+-?)-(.+?)/?$', 'index.php?p=$matches[2]&post_type=variety_vip_report', 'top' );

		// Rule for VIP Home Page : 'vip/'.
		add_rewrite_rule( '^vip/?$', 'index.php?pagename=vip', 'top' );

	}



}
