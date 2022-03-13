<?php
// Set constants.
define( 'PMC_TABOOLA_PLUGIN_URL', trailingslashit( plugins_url( null, __FILE__ ) ) );
define( 'PMC_TABOOLA_PLUGIN_DIR', __DIR__ );
define( 'PMC_TABOOLA_VERSION', '2021.1' );

// Instantiate classes.
PMC\Taboola\Taboola_Render::get_instance();

// EOF
