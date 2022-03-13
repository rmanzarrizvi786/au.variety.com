<?php
// Set constants.
define( 'PMC_CEROS_PLUGIN_URL', trailingslashit( plugins_url( null, __FILE__ ) ) );
define( 'PMC_CEROS_PLUGIN_DIR', __DIR__ );

// Instantiate classes.
PMC\Ceros\Admin_UI::get_instance();
PMC\Ceros\Frontend::get_instance();

// EOF
