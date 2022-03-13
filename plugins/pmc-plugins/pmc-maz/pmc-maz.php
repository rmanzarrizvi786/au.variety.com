<?php
/**
 * Plugin Name: PMC Maz
 *
 * Plugin URI: http://pmc.com/
 *
 * Mainfest file for PMC Maz plugin.
 *
 * Author: PMC, Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * Description: This plugin is used provide custom maz endpoint.
 *
 * Version: 1.0
 *
 * License: PMC Proprietary. All rights reserved.
 *
 * @package pmc-maz
 */

define( 'PMC_MAZ_ROOT', __DIR__ );

define( 'PMC_MAZ_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

require_once( PMC_MAZ_ROOT . '/dependencies.php' );
require_once( PMC_MAZ_ROOT . '/helper-functions.php' );
require_once( PMC_MAZ_ROOT . '/classes/class-plugin.php' );
require_once( PMC_MAZ_ROOT . '/classes/class-settings.php' );

\PMC\Maz\Plugin::get_instance();
\PMC\Maz\Settings::get_instance();


