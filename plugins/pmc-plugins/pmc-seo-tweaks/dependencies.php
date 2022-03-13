<?php

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// This plugin relies on the PMC Post Options Plugin
// Let's ensure it's been loaded, and if not—let's load it
$loaded_plugins = wpcom_vip_get_loaded_plugins();
if ( ! in_array( 'pmc-plugins/pmc-post-options', $loaded_plugins ) ) {
	if ( function_exists( 'wpcom_vip_load_plugin' ) ) {
		wpcom_vip_load_plugin( 'pmc-post-options', 'pmc-plugins' );
	}
}