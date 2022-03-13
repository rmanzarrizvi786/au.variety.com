<?php
/**
 * Class for Whizzco WP Widget
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-03-25
 */


namespace PMC\Vendor_Widgets\Whizzco;

use \WP_Widget;

class Widget extends WP_Widget {

	const ID = 'pmc-vw-whizzco-wp-widget';

	/**
	 * class constructor
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		parent::__construct( self::ID, 'PMC Whizzco Widget' );
	}

	/**
	 * Method to setup values for config vars for the widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return bool Returns TRUE if vars are successfully set else FALSE
	 */
	protected function _setup_vars( $args, $instance ) : bool {

		$key        = (int) apply_filters( 'pmc_vendor_widgets_whizzco_wp_key', 0, $args, $instance );
		$widget_id  = (int) apply_filters( 'pmc_vendor_widgets_whizzco_wp_widget_id', 0, $args, $instance );
		$website_id = (int) apply_filters( 'pmc_vendor_widgets_whizzco_wp_website_id', 0, $args, $instance );

		return UI::get_instance()->set_var_overrides( $key, $widget_id, $website_id );

	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		if ( ! $this->_setup_vars( $args, $instance ) ) {
			return;
		}

		UI::get_instance()->render_widget( true );

	}

}    //end class


//EOF
