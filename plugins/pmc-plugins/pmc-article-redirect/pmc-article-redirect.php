<?php
/*
Plugin Name: PMC Article Redirect
Description: Adds an action hook to check if we have a valid post Id at the end of the article url if we get a 404 page then redirect to correct article page.
Version: 1.0
Author: PMC, Archana Mandhare
License: PMC Proprietary. All rights reserved.
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
if ( ! is_admin() && ! is_preview() ) {
	PMC\Article_Redirect\Validate::get_instance();
}