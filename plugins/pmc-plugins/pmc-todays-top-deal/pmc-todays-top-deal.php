<?php
// Set constants.
define( 'PMC_TODAYS_TOP_DEAL_PLUGIN_URL', trailingslashit( plugins_url( null, __FILE__ ) ) );
define( 'PMC_TODAYS_TOP_DEAL_PLUGIN_DIR', __DIR__ );
define( 'PMC_TODAYS_TOP_DEAL_VERSION', '2021.1' );

// Instantiate classes.
PMC\Todays_Top_deal\Admin::get_instance();
PMC\Todays_Top_deal\Shortcode::get_instance();

// EOF
