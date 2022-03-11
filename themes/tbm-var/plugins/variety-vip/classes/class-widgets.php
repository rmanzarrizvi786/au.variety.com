<?php
/**
 * Widgets
 *
 * Responsible for widget functionality.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Widgets
 */
class Widgets {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() {

		add_action( 'widgets_init', [ $this, 'register_sidebars' ] );
		add_action( 'widgets_init', [ $this, 'load_widgets' ] );

	}

	/**
	 * Register Sidebars
	 *
	 * Register sidebars for the site.
	 */
	public function register_sidebars() {

		register_sidebar(
			array(
				'name'          => esc_html__( 'VIP Home - Featured Chart', 'pmc-variety' ),
				'id'            => 'vip_home_featured_chart',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			)
		);

	}

	/**
	 * Load Widgets.
	 */
	public function load_widgets() {
		register_widget( '\Variety\Plugins\Variety_VIP\Widgets\VIP_Featured_Chart' );
	}

}

// EOF.
