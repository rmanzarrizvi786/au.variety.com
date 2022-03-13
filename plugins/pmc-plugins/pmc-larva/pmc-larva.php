<?php
/**
 * Plugin Name: PMC Larva
 * Plugin URI: http://www.pmc.com
 * Description: PMC's Design System
 * Version: 1.0
 * Author: PMC Team
 * License: PMC Proprietary. All rights reserved.
 */

namespace PMC\Larva;

define( 'PMC_LARVA_ACTIVE', true );

define( 'PMC_LARVA_PLUGIN_PATH', untrailingslashit( __DIR__ ) );
define( 'PMC_LARVA_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

define( 'PMC_LARVA_THEME_PATH', untrailingslashit( get_stylesheet_directory() ) );
define( 'PMC_LARVA_THEME_URL', untrailingslashit( get_stylesheet_directory_uri() ) );

require_once __DIR__ . '/functions.php';

Core_Assets::get_instance();
Hooks::get_instance();
