<?php
/*
Plugin Name: PMC Tag Links
Plugin URI: http://pmc.com/
Description: A plugin to link first occurrence (in post content) of each tag of a post to that tag's archive
Version: 3.1
Author: Amit Gupta
*/

/**
 * load up the plugin class
 */
require_once( __DIR__ . '/class-pmc-tag-links.php' );

/**
 * setup the plugin loader function to be called on WordPress init
 */
add_action( 'init', 'pmc_tag_links_loader' );

/**
 * Plugin loader.
 * Initialize PMC_Tag_Links on WordPress init if its not already initialized
 *
 * @since 0.1
 * @version 0.2
 */
function pmc_tag_links_loader() {
	if ( ! isset( $GLOBALS['pmc_tag_links'] ) ) {
		$GLOBALS['pmc_tag_links'] = new PMC_Tag_Links();
	}
}


//EOF
