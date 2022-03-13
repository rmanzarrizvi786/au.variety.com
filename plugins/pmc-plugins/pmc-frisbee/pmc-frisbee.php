<?php
/*
Plugin Name: PMC Frisbee
Plugin URI: https://github.com/sheknows/frisbee
Description: Frisbee is a small cross-browser data collection library. This plugin only loads the js file. Configuration, initiation and data collection need to happen in that specific implementation.
Version: 1.0
Author: SheKnows, Dan Berko
License: PMC Proprietary. All rights reserved.

PMC Quantcast CMP plugin loads this as a dependency.
*/

/* The javascript files are compiled locally from the repo and committed here.
	1. git clone https://github.com/sheknows/frisbee.git
	2. yarn install
	3. yarn run dev and yarn run build
	4. Copy the compiled js files into assets/js
	5. Update the PMC_FRISBEE_VER to bust cache on the front end.
 */
define( 'PMC_FRISBEE_VER', '0.2.0' ); // based on frisbee package version
define( 'PMC_FRISBEE_SRC', plugins_url( 'assets/js/frisbee.js', __FILE__ ) );
define( 'PMC_FRISBEE_SRC_MIN', plugins_url( 'assets/js/frisbee.min.js', __FILE__ ) );

/*
 * Initialize Frisbee JS inclusion
 */
\PMC\Frisbee\Admin::get_instance();
