<?php
/*
 * Plugin Name: PMC NDN
 * Plugin URI: http://pmc.com/
 * Description: Plugin PMC NDN (New Device Notifications) offers a service to send an email anytime a user logs into the admin on a new device.
 * Version: 1.0
 * Author: Archana Mandhare, PMC
 * License: PMC Proprietary.  All rights reserved.
 *
 */

define( 'PMC_NDN_DIR', __DIR__ );

function pmc_ndn_loader() {
	/*
	 * Load up plugin dependencies
	 */
	require_once PMC_NDN_DIR . '/dependencies.php';

	PMC\NDN\Notify::get_instance();
}
pmc_ndn_loader();

//EOF
