<?php

/**
 * Plugin Name: PMC 410
 * Plugin URI: http://pmc.com/
 * Description: Plugin to set 410 status on selected url.
 * For adding any new url to set 410 you just needs to prepare a csv file with two columns
 * and submit to VIP to import data for 'wpcom-legacy-redirector'
 * ie 'from_url' and 'to_url' same as we do for 301 redirects except 'to_url' is set to fixed string '/pmc-410'.
 * Example record in csv- '/2015/07/22/sample-url/',/pmc-410
 *
 * Version: 1.0
 * Author: PMC, Vinod Tella
 * License: PMC Proprietary. All rights reserved.
 *
 */

define( 'PMC_410_ROOT', __DIR__ );

require_once PMC_410_ROOT . '/dependencies.php';
require_once PMC_410_ROOT . '/classes/class-plugin.php';

PMC\PMC_410\Plugin::get_instance();

