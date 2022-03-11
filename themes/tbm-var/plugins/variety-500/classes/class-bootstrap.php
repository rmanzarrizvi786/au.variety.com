<?php
/**
 * Adds Variety 500 functionality to the Variety.com website and
 * allows executive profiles to be marked as a 500 profile.
 * Also includes a search feature and templating.
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Variety_500;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Bootstrap
 *
 * Simple class used for initializing the plugin.
 *
 * @since 2017-08-17
 */
class Bootstrap {

	use Singleton;

	/**
	 * Cache Group
	 *
	 * @var string The name of the cache namespace across all classes.
	 */
	const CACHE_GROUP = 'variety_500';

	/**
	 * Class constructor.
	 *
	 * Initializes the plugin and gets things started on the `init` action.
	 *
	 */
	protected function __construct() {

		/*
		 * Init our plugin late since we need to have the taxonomies and other
		 * plugins that we rely on set up first.
		 */
		add_action( 'init', array( $this, 'setup' ), 20 );

		// Load after PMC SEO Tweaks plugin.
		add_action( 'robots_txt', array( $this, 'add_swiftbot_to_robots_txt' ), 12 );

	}

	/**
	 * Setup
	 *
	 * Is responsible for initializing the different pieces of functionality
	 * needed for the Variety 500 functionality. Since the classes uses a
	 * Singleton pattern, we just need to call `get_instance` on each class that
	 * we want to initialize.
	 *
	 */
	public function setup() {
		Settings::get_instance();
		Templates::get_instance();
		Profile::get_instance();
		Stats::get_instance();
		Search::get_instance();
		Sharing::get_instance();
		Interviews::get_instance();
	}

	/**
	 * Add Swiftbot to robots.txt.
	 *
	 * The Variety Dev environments disallow all bots, so we allow Swiftbot
	 * access for this plugin's Search to function.
	 *
	 * This is not using the pmc_robots_txt filter, as
	 * it loads before this plugin does.
	 *
	 * @param string $output The Output string.
	 *
	 * @todo Remove this before deploying to production, CDWE-502
	 *
	 * @return string The Output string.
	 */
	public function add_swiftbot_to_robots_txt( $output ) {
		if ( ! \PMC::is_production() ) {
			if ( ! defined( 'PMC_IS_PRODUCTION' ) || false === PMC_IS_PRODUCTION ) {
				$output .= PHP_EOL . PHP_EOL . 'User-agent: Swiftbot' . PHP_EOL;
				$output .= 'Allow: *' . PHP_EOL;
			}
		}
		return $output;
	}
}

// EOF.
