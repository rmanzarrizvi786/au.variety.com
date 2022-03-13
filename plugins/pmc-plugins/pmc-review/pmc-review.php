<?php
/**
 * Plugin Name: PMC Review
 * Plugin URI: http://www.pmc.com
 * Version: 1.0
 * Author: John Watkins, XWP
 * Author URI: http://www.xwp.co
 * john.watkins@xwp.co
 * License: PMC Proprietary. All rights reserved.
 */

define( 'PMC_REVIEW_DIR', __DIR__ );
define( 'PMC_REVIEW_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

function pmc_review_loader() {

	require_once PMC_REVIEW_DIR . '/dependencies.php';

	\PMC\Review\Fields::get_instance();
	\PMC\Review\Review::get_instance();
	\PMC\Review\Snippet::get_instance();
	\PMC\Review\Json_Data::get_instance();

}

pmc_review_loader();

//EOF
