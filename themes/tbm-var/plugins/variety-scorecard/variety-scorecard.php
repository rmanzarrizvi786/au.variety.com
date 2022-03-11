<?php
/**
 * Plugin Name: Variety Scorecard - Pilots
 * Plugin URI: http://www.variety.com
 * Description: Scorecard - Pilots
 * Version: 1.0
 * Author: Hau Vong
 * Author URI: http://www.pmc.com
 * Author Email: hvong@pmc.com
 * License: PMC proprietary. All rights reserved.
 *
 * @package Variety\Plugins\Variety_Scorecard
 */

// Load plugin code.
require_once( __DIR__ . '/class-variety-scorecard-settings.php' );
require_once( __DIR__ . '/class-variety-scorecard-api.php' );
require_once( __DIR__ . '/class-variety-scorecard.php' );

// Grab or create a new instance of our Variety Scorecard class.
Variety_Scorecard::get_instance();

// EOF
