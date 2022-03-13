<?php
/**
 * Plugin Name: PMC FAQ.
 * Description: FAQ feature for any post type.
 * Author: PMC
 * License: PMC Proprietary. All rights reserved.
 *
 * @package pmc-plugins
 */
namespace PMC\FAQ;

define( 'PMC_FAQ_PATH', untrailingslashit( __DIR__ ) );
define( 'PMC_FAQ_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'PMC_FAQ_VERSION', '2021.1' );

require_once( __DIR__ . '/dependencies.php' );

Fields::get_instance();
Utility::get_instance();
