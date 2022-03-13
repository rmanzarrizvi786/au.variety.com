<?php
/*
Plugin Name: PMC Sharable Images
Plugin URI: http://www.pmc.com
Description: Adds a script for sharing article content images via pinterest
Version: 1.0
Author: PMC, Tom Harrigan
License: PMC Proprietary. All rights reserved.
*/


define( 'PMC_SHARABLE_IMAGES_URL', plugins_url( '', __FILE__ ) );

PMC\Sharable_Images\Setup::get_instance();

// EOF
