<?php
/*
Plugin Name: PMC Outbrain Widget
Description: Outbrain HTML tags for article and gallery pages for desktop and mobile
Version: 1.0
Author: PMC, Archana Mandhare <amandhare@pmc.com>
License: PMC Proprietary.  All rights reserved.
*/

define( 'PMC_OUTBRAIN_ROOT', __DIR__ );

// Instantiate singletons
PMC\Outbrain\Setup::get_instance();

// Register widget
add_action( 'widgets_init', function () {
	register_widget( "PMC\Outbrain\Widget" );
} );

//EOF

