<?php
/**
 * Plugin Name: PMC Styled Heading
 * Plugin URI: http://www.pmc.com
 * Version: 1.0
 * Author: John Watkins, XWP
 * Author URI: http://www.xwp.co
 * Author Email: john.watkins@xwp.co
 * License: PMC Proprietary. All rights reserved.
 */

define( 'PMC_STYLED_HEADING_URL', plugin_dir_url( __FILE__ ) );
define( 'PMC_STYLED_HEADING_PATH', plugin_dir_path( __FILE__ ) );

function pmc_styled_heading_loader() {
	require_once PMC_STYLED_HEADING_PATH . 'dependencies.php';
}

pmc_styled_heading_loader();

//EOF
