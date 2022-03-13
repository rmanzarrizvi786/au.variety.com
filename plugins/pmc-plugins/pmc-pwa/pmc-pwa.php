<?php
/**
 * Plugin Name: PMC PWA
 * Plugin URI: http://www.pmc.com
 * Description: Integrate Progressive Web App (PWA) features.
 * Version: 1.0
 * Author: PMC
 * License: PMC Proprietary. All rights reserved.
 *
 * @package pmc-pwa
*/

namespace PMC\PWA;

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// Limit to compatible environments.
if ( ! Utils::plugin_is_supported() ) {
	return;
}

// This must be loaded first as some of what we override is not executed in a hook callback.
Core_Overrides::get_instance();

pmc_load_plugin( 'pwa', 'pmc-plugins' );

// All remaining classes should be instantiated here, when necessary.
Components::get_instance();
Enqueued_Assets::get_instance();
Overrides::get_instance();
Amp_Overrides::get_instance();
Service_Worker_Cache::get_instance();
Theme::get_instance();
