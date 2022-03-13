<?php
/**
 * PMC Iframe widget for sidebar.
 *
 * @package pmc-iframe-widget
 */

namespace PMC\Iframe_Widget;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Plugin
 */
class Plugin {

	use Singleton;

	/**
	 * Plugin constructor.
	 */
	protected function __construct() {

		// Code to add wp hooks.
		add_action( 'widgets_init', [ $this, 'action_widgets_init' ] );
	}

	/**
	 * Widgets initialization.
	 */
	public function action_widgets_init() {

		register_widget( Widget::class );
	}

}
