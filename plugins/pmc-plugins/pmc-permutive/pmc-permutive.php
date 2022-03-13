<?php
/**
 * Plugin Name: PMC Permutive
 * Description: Plugin for permutive page events and custom event tracking
 * Version: 1.0.0
 * @package pmc-plugins
 */

namespace PMC\Permutive;

use PMC\Permutive\Plugin;

define( 'PMC_PERMUTIVE_VERSION', '1.0' );
define( 'PMC_PERMUTIVE_DIR', trailingslashit( __DIR__ ) );

// Initiate the Permutive class
Plugin::get_instance();
