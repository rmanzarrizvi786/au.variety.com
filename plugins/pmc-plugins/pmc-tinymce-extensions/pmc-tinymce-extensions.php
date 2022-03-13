<?php
/**
 * Plugin Name: PMC TinyMCE Extensions
 * Description: Extend TinyMCE with a few goodies.
 * Author: Luke Woodward, 10up
 * Author URI: http://10up.com
 */
class PMC_TinyMCE_Extensions {
	public static function init() {
		add_shortcode( 'pmc_hidden_text', '__return_null' );
		add_filter( 'mce_external_plugins', array( __CLASS__ , 'external_plugins' ) );
		add_filter( 'mce_buttons', array( __CLASS__, 'mce_buttons' ) );
		add_action( 'admin_init', array( __CLASS__, 'style' ) );
		add_filter( 'mce_css', array( __CLASS__, 'mce_style' ) );
	}
	public static function external_plugins( $plugins ) {
		$plugins['pmcHiddenText'] = plugins_url( 'pmc-tinymce-extensions/js/editor_plugin.js', __DIR__ );
		$plugins['searchreplace'] = plugins_url( 'pmc-tinymce-extensions/searchreplace/editor_plugin.js', __DIR__ );
		return $plugins;
	}
	public static function mce_buttons( $buttons ) {
		$buttons[] = 'pmc-hidden-text';
		$buttons[] = 'search';
		return $buttons;
	}
	public static function style() {
		wp_enqueue_style( 'pmc-hidden-text', plugins_url( 'pmc-tinymce-extensions/css/pmc-tinymce-extensions.css', __DIR__ ) );
	}
	public static function mce_style( $mce_css ) {
		if ( ! empty( $mce_css ) )
			$mce_css .= ',';

		$mce_css .= plugins_url( '/css/pmc-tinymce-editor.css', __FILE__ );

		return $mce_css;
	}
}
add_action( 'init', array( 'PMC_TinyMCE_Extensions', 'init' ) );