<?php

/**
 * Plugin Name: PMC ULS
 * Plugin URI: http://www.pmc.com/
 * Version: 1.0
 * Author: Hau Vong (hvong@pmc.com)
 * License: PMC Proprietary.  All rights reserved.
 */


define( 'PMC_ULS_VERSION', '1.2' );
define( 'PMC_ULS_DIR', __DIR__ );
define( 'PMC_ULS_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'PMC_ULS_KEY', '5fc34527ee2aebb6dd107909d3af377c' );
define( 'PMC_ULS_SECRET', '8d4a1fc91ddb5da332618b086c08a4a9' );

require_once __DIR__ . '/dependencies.php';

PMC\Uls\Plugin::get_instance();

if ( PMC\Uls\Plugin::get_instance()->is_go() ) {

	// Ignore because our pipelines don't presently load Go mu-plugins
	PMC\Uls\Caching::get_instance(); // @codeCoverageIgnore
}
