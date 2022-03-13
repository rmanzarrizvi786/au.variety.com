<?php

/**
 * Plugin Name: Woobox
 * Plugin URI: http://woobox.com/
 * Description: Embed Woobox promotions using a shortcode. Usage: [woobox offer='abcdef']
 * Version: 1.0
 * Author: Woobox
 * Author URI: http://woobox.com
 * License: GPL
 */

use \PMC\Global_Functions\Traits\Singleton;

class Woobox {

	use Singleton;

	protected function __construct() {
		add_shortcode( 'woobox', array( $this, 'create_woobox_embed' ) );

		// Temporarily remove this admin_head hook while we sort out
		// why this breaks tinymce editor toolbar buttons
		// add_action( 'admin_head', array( $this, 'woobox_button' ) );
	}

	public function create_woobox_embed( $user_defined_attributes, $content = null ) {
		$defaults = array(
			'offer' => ''
		);

		$attributes = shortcode_atts( $defaults, $user_defined_attributes );

		if ( ! empty( $attributes['offer'] ) ) {
			wp_enqueue_script( 'woobox-sdk', plugins_url( '/woobox_requiresdk.js', __FILE__ ), array( 'jquery' ), false, false );
			$embed_code = "<div class='woobox-offer' data-offer='" . esc_attr( $attributes['offer'] ) . "'></div>";
		} else {
			$embed_code = "";
		}

		return $embed_code;
	}

	public function woobox_button() {
		global $typenow;
		if ( ! in_array( $typenow, array( 'post', 'page' ) ) ) {
			return;
		}

		add_filter( 'mce_external_plugins', 'add_woobox_button_js' );
		add_filter( 'mce_buttons', 'add_woobox_button_key' );
	}

	public function add_woobox_button_js( $plugin_array ) {
		$plugin_array['woobox_button'] = plugins_url( '/woobox_tinymce.js', __FILE__ );

		return $plugin_array;
	}

	public function add_woobox_button_key( $buttons ) {
		array_push( $buttons, 'woobox_button' );

		return $buttons;
	}

}

Woobox::get_instance();
