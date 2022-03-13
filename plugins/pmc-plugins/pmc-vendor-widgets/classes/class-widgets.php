<?php
/**
 * Class for registering WP Widgets
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-03-25
 */


namespace PMC\Vendor_Widgets;


use \PMC\Global_Functions\Traits\Singleton;


class Widgets {

	use Singleton;

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
		add_action( 'widgets_init', [ $this, 'register_with_wp' ] );

	}

	/**
	 * Method to register widgets with WP
	 *
	 * @return void
	 */
	public function register_with_wp() : void {

		// Whizzco widget
		register_widget( '\PMC\Vendor_Widgets\Whizzco\Widget' );

	}

}    //end class

//EOF
