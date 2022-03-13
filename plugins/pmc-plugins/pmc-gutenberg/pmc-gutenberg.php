<?php
/**
 * Plugin Name: PMC Gutenberg
 * Plugin URI: http://www.pmc.com
 * Description: Gutenberg, but for PMC
 * Version: 1.0
 * Author: PMC Team
 * License: PMC Proprietary. All rights reserved.
 */

namespace PMC\Gutenberg;

define(
	'PMC_GUTENBERG_PLUGIN_PATH',
	trailingslashit(
		plugin_dir_path( __FILE__ )
	)
);
define(
	'PMC_GUTENBERG_PLUGIN_URL',
	trailingslashit(
		plugin_dir_url( __FILE__ )
	)
);
define( 'PMC_GUTENBERG_BUILD_DIR_SLUG', 'build/' );

// IMPORTANT: This file should only contain dependency includes and plugin activation code.
// For unit test, we must absolutely not duplicate this call anywhere in the unit test.
Gutenberg::get_instance();
Block_Editor_Settings::get_instance();
Classic_Editor_Compat::get_instance();
REST_API\Modifications::get_instance();
