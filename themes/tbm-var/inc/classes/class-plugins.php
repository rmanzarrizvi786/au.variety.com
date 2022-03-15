<?php

/**
 * Plugins
 *
 * Load plugins.
 *
 * @package pmc-variety
 *
 * @since   2018-12-26
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Plugins
 *
 * @since 2018-07-05
 * @see   \PMC\Global_Functions\Traits\Singleton
 */
class Plugins
{

	use Singleton;

	/**
	 * Method to load any local theme plugins
	 *
	 * @return void
	 */
	public function load_local_plugins()
	{

		// Configs.
		require_once PMC_CORE_PATH . '/plugins/_config/_manifest.php';
		require_once CHILD_THEME_PATH . '/plugins/_config/_manifest.php';

		// Variety-specific plugins.
		require_once(CHILD_THEME_PATH . '/plugins/exclusive-articles/exclusive-articles.php');
		require_once(CHILD_THEME_PATH . '/plugins/pmc-mobile-api/vy-mobile-api.php'); // phpcs:ignore
		require_once(CHILD_THEME_PATH . '/plugins/sponsored-content/sponsored-content.php');
		require_once(CHILD_THEME_PATH . '/plugins/variety-500/variety-500.php');
		// require_once(CHILD_THEME_PATH . '/plugins/variety-authentication/variety-authentication.php');
		// require_once(CHILD_THEME_PATH . '/plugins/variety-digital-subscriber/variety-digital-subscriber.php');
		require_once(CHILD_THEME_PATH . '/plugins/variety-hollywood-executives/variety-hollywood-executives.php');
		// require_once(CHILD_THEME_PATH . '/plugins/variety-print-issue/variety-print-issue.php');
		require_once(CHILD_THEME_PATH . '/plugins/variety-production-grid/variety-production-grid.php');
		require_once(CHILD_THEME_PATH . '/plugins/variety-scorecard/variety-scorecard.php');
		require_once(CHILD_THEME_PATH . '/plugins/variety-subscriptions/class-variety-subscriptions-helper.php');
		require_once(CHILD_THEME_PATH . '/plugins/variety-top-videos/variety-top-videos.php');
		require_once(CHILD_THEME_PATH . '/plugins/variety-vscore-top/variety-vscore-top.php');
		// require_once( CHILD_THEME_PATH . '/plugins/variety-vip/variety-vip.php' );

		require_once(CHILD_THEME_PATH . '/plugins/tbm-adm/tbm-adm.php');
	}
}
