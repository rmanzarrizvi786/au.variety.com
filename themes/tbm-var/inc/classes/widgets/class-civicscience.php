<?php
/**
 * Civic Science widget
 *
 * @package pmc-variety-2017
 * @since 2019.07.30
 */

namespace Variety\Inc\Widgets;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Civicscience
 *
 * @since 2019.07.30
 */
class Civicscience {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Initializes the theme.
	 *
	 * @since 2019.07.30
	 */
	public function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @since  2019-07-30 - Muhammad Muhsin - BR-237
	 *
	 * @return void
	 */
	public function _setup_hooks() {

		/**
		 * Actions
		 */
		add_action( 'variety_single_after_content', [ $this, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );

	}

	/**
	 * Enqueue Civicscience script.
	 */
	public function enqueue_script() {

		if ( is_single() ) {
			// Civicscience script.
			wp_enqueue_script( 'pmc-async-civicscience-js', 'https://www.civicscience.com/jspoll/4/civicscience-widget.js', [], false, true );
		}

	}

	/**
	 * Return Civicscience code.
	 */
	public function render() {

		echo '<div id="civsci-id-664689317" data-civicscience-widget="aba129dc-fa8e-df34-65dd-358954a8b035"></div>';

	}
}
