<?php
/**
 * Plugin Name: PMC Onetrust
 * Description: Plugin for OneTrust consent banner
 * Version: 1.0.0
 * @package pmc-plugins
 */

namespace PMC\Onetrust;

define( 'PMC_ONETRUST_VERSION', '1.0' );
define( 'PMC_ONETRUST_DIR', trailingslashit( __DIR__ ) );

require_once PMC_ONETRUST_DIR . 'dependencies.php';

// Initiate the Onetrust class
Onetrust::get_instance();
