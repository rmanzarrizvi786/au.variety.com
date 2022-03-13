<?php
/**
* Plugin Name: PMC iFrame Widget
* Plugin URI: http://www.pmc.com
* Description: Adds a post option to mark a post as sticky post and excludes the sticky posts from home river
* Version: 1.1
* License: PMC Proprietary. All rights reserved.
* @package pmc-plugins
*/

define( 'PMC_IFRAME_WIDGET_PATH', untrailingslashit( __DIR__ ) );
define( 'PMC_IFRAME_WIDGET_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'PMC_IFRAME_WIDGET_VERSION', '2021.1' );
require_once( __DIR__ . '/dependencies.php' );

\PMC\Iframe_Widget\Plugin::get_instance();
