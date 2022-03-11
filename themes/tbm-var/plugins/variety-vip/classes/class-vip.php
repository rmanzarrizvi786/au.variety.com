<?php
/**
 * Bootstraps Variety Intelligence Platform functionality.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class VIP
 *
 * Simple class used for initializing the plugin.
 */
class VIP {

	use Singleton;

	private $_user_data;

	/**
	 * Cache Group
	 *
	 * @var string The name of the cache namespace across all classes.
	 */
	const CACHE_GROUP = 'variety_vip';

	/**
	 * Class constructor.
	 *
	 * Initializes the plugin and gets things started on the `init` action.
	 *
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 *
	 */
	protected function _setup_hooks() {

		/*
		 * Init our plugin late since we need to have the taxonomies and other
		 * plugins that we rely on set up first.
		 */
		add_action( 'init', array( $this, 'setup' ), 20 );

		// We need to initiate some actions like rewrite rules before priority 20 of init.
		add_action( 'init', [ Rewrites::get_instance(), 'on_action_init' ], 0 );

		// Initiate widgets earlier than `init - 20`
		Widgets::get_instance();

	}

	/**
	 * Setup and initiate our classes.
	 */
	public function setup() {

		Assets::get_instance();
		Content::get_instance();
		Author::get_instance();
		Video::get_instance();
		Menus::get_instance();
		Special_Reports::get_instance();
		Event::get_instance();
		Related::get_instance();
		Rewrites::get_instance();
		Injection::get_instance();

		$this->add_body_class();

	}

	/**
	 * @codeCoverageIgnore
	 */
	public static function vip_url() {
		return home_url( '/vip/' );
	}

	/**
	 * @codeCoverageIgnore
	 */
	public static function vip_subscription_url() {
		if ( \PMC::is_production() ) {
			return 'https://subscriptions.pmc.com/checkout/vyvip/';
		}
		return 'https://subscriptions.pmcdev.io/checkout/vyvip/';
	}

	/**
	 * @codeCoverageIgnore
	 * @return mixed
	 */
	public function get_user_data() {

		if ( ! empty( $this->_user_data->acct->name ) ) {

			return $this->_user_data->acct->name;
		}
	}

	/**
	 * @codeCoverageIgnore
	 * Add any special body classes here
	 *
	 * @return array $classes
	 */
	public function add_body_class() {

		$this->_user_data = \PMC\Subscription_V2\User::get_instance()->get_user_data();

		if ( ! empty( $this->_user_data->acct->name ) ) {
			$css_classes = [ 'authenticated', 'authenticated-vip' ];
			pmc_add_body_class( $css_classes );
		}

	}
}

// EOF.
