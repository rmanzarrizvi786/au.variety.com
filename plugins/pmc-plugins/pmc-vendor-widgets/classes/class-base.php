<?php
/**
 * Base class for vendor widgets
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-03-25
 */


namespace PMC\Vendor_Widgets;

use \PMC\Global_Functions\Traits\Singleton;

abstract class Base {

	use Singleton;

	/**
	 * @var bool Flag to determine whether to load the widget assets on current page or not
	 */
	protected $_should_load_assets = false;

	/**
	 * class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to set up listeners to WP hooks
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() : void {

		/*
		 * Actions
		 */

		// This is being done on footer because enqueue hook runs in head and our widget render
		// would not have been called by then. We want to load assets only if widget is rendered.
		add_action( 'wp_footer', [ $this, 'enqueue_stuff' ] );

	}

	/**
	 * Method to check if assets for the widget should be loaded on the current page or not
	 *
	 * @return bool
	 */
	protected function _should_load_assets() : bool {
		return (bool) $this->_should_load_assets;
	}

	/**
	 * Method to set assets for the widget to be loaded on the current page
	 *
	 * @return void
	 */
	public function mark_assets_for_loading() : void {
		$this->_should_load_assets = true;
	}

	/**
	 * Method called on wp_enqueue_scripts hook to load up assets on current page
	 *
	 * @return void
	 */
	abstract public function enqueue_stuff() : void;

	/**
	 * Method to render widget
	 *
	 * @return void
	 */
	abstract public function render_widget() : void;

}    //end class


//EOF
