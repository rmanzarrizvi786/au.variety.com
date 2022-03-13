<?php
/*
Plugin Name: PMC Term Meta Plugin
Description: This plugin provides an api to save meta info in taxonomy terms
Version: 1.0
Author: PMC, Amit Gupta
License: PMC Proprietary.  All rights reserved.
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
add_action( 'init', 'pmc_term_meta_loader', 12 );	//allow for reg of custom taxonomies

function pmc_term_meta_loader() {
	/**
	 * load up plugin classes
	 */
	require_once( __DIR__ . '/class-pmc-term-meta.php' );

	if ( ! array_key_exists( 'pmc_term_meta', $GLOBALS ) ) {
		$GLOBALS['pmc_term_meta'] = PMC_Term_Meta::get_instance();
	}
}



//EOF