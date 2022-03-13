<?php
/*
Plugin Name: PMC Disable Live Chat
Description: Disables the chat in wp-admin for users who opt for it
Version: 1.0
Author: PMC, Amit Gupta
License: PMC Proprietary.  All rights reserved.
*/


define( 'PMC_DISABLE_LIVE_CHAT_ROOT', __DIR__ );

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

function pmc_disable_live_chat_loader() {
	/*
	 * Initialize Admin class
	 */
	PMC\Disable_Live_Chat\Admin::get_instance();
}

pmc_disable_live_chat_loader();


//EOF