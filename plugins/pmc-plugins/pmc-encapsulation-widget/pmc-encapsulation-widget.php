<?php
/*
	Plugin Name: PMC Encapsulation Widget
	Plugin URI: http://www.pmc.com/
	Description: Parent widget to encapsulate widgets and render their contents
	Version: 1.0
	Author: PMC, Amit Sannad
	Author URI: http://www.pmc.com/
	License: PMC Proprietary.  All rights reserved.
*/

class Pmc_Encapsulation_Widget extends WP_Widget {

	private $_widget_name = '';

	private $_widget_title = '';

	private $_widget_description = 'General Widget to render static content';

	function __construct() {

		parent::__construct( false, $this->_widget_name, array( 'description' => __( $this->_widget_description ), ) );
	}

	function set_widget_property( $widget_name, $widget_title, $widget_description="" ) {

		$this->_widget_name = $widget_name;
		$this->_widget_title = $widget_title;
		if( !empty( $widget_description  ) )
			$this->_widget_description = $widget_description;
	}

	public function widget( $args, $instance ) {
		extract( $args );

		echo $before_widget;

		if ( !empty( $this->_widget_title ) ) {

			$title = apply_filters( 'widget_title', $this->_widget_title, $instance, $this->id_base );

			echo $before_title . $title . $after_title;
		}

		$this->widget_content();

		echo $after_widget;
	}
}
// EOF