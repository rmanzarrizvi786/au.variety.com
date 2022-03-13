<?php
/**
 * Plugin Name: PMC Automated Related Links
 * Description: A plugin to allow authors to add customizable related links to articles
 * Version: 1.0
 * Author: PMC, Amit Gupta
 * License: PMC Proprietary.  All rights reserved.
 */

define( 'PMC_AUTOMATED_RELATED_LINKS_PLUGIN_PATH', untrailingslashit( __DIR__ ) );
define( 'PMC_AUTOMATED_RELATED_LINKS_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'PMC_AUTOMATED_RELATED_LINKS_JS_VERSION', '2022.1.0' );

require_once( PMC_AUTOMATED_RELATED_LINKS_PLUGIN_PATH . '/dependencies.php' );
require_once( PMC_AUTOMATED_RELATED_LINKS_PLUGIN_PATH . '/compatibilities.php' );

\PMC\Automated_Related_Links\Plugin::get_instance();
\PMC\Automated_Related_Links\Frontend::get_instance();
\PMC\Automated_Related_Links\Shortcode::get_instance();
