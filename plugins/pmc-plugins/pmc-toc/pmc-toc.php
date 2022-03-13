<?php
/**
 * Plugin Name: PMC TOC.
 * Description: Adds a table of contents to your posts based on a specified tag.
 * Author: PMC
 * License: PMC Proprietary. All rights reserved.
 *
 * @package pmc-plugins
 */
namespace PMC\TOC;

define( 'PMC_TOC_PATH', untrailingslashit( __DIR__ ) );
define( 'PMC_TOC_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'PMC_TOC_VERSION', '1.0' );

require_once( __DIR__ . '/dependencies.php' );

Setup::get_instance();
