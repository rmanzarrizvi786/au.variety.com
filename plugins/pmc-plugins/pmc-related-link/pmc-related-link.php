<?php
/**
 * Plugin Name: PMC Related Link
 * Plugin URI: http://pmc.com/
 * Description: Creates a 'Related Link' button within the TinyMCE Editor while editing any post type, which inserts a shortcode instead of the default <a> tag.
 * Version: 0.1
 * Author: James Mehorter @ 10up / james@10up.com
 * License: PMC Proprietary.  All rights reserved.
 */

define( 'PMC_RELATED_LINK_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'PMC_RELATED_LINK_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

// Load dependencies.
require_once( __DIR__ . '/dependencies.php' );

// Load the PMC Related Link class.
require_once( __DIR__ . '/classes/class-pmc-related-link.php' );

// Instance the PMC Related Link class.
PMC_Related_Link::get_instance();
