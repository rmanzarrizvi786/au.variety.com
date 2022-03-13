<?php
/**
 * Plugin Name: PMC Ajax Pagination
 * Plugin URI: http://pmc.com
 * Description: Enables Ajax Pagination for any set of data.
	Just needs a function which accepts 2 parameters
	1. An id for targetting data
	2. The page Number
	and returns data in the format
	array(
		'html' => 'html data to show for id and page',
		'pages' => 'no of pages of data'
	)

	Handles the pagination backend and frontend
	Uses crawler friendly #! also
	Handles Google's _escaped_fragment_

	Can handle Multiple types of Pagination
		Numeric
		Prev - Next
		Previous Numeric Next
	Selected Classes are assigned to current page as well as prev next as needed

 * Version: 1.0.0
 * Author: PMC, vickybiswas
 * Author URI: http://www.pmc.com/
 * Author Email: vbiswas@pmc.com
 * License: PMC Proprietary.  All rights reserved.
 */

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
/**
 * load up the plugin class
 */
require_once( __DIR__ . '/class_pmc_ajax_pagination.php' );
