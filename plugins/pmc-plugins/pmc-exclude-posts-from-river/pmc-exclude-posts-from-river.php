<?php
/*
 * Plugin Name: PMC Exclude Posts From River
 * Plugin URI: http://pmc.com/
 *
 * Description: This plugin adds a toggle flag to publish meta box to allow exclusion of post(s) from the river
 *
 * Version: 2.0
 * Author: Adaeze Esiobu, Amit Gupta
 */

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

function pmc_exclude_posts_from_river_loader() {
	/*
	 * Load up class(es)
	 */
	require_once __DIR__ . '/class-pmc-exclude-posts-from-river.php';

	/*
	 * Initialize plugin
	 */
	PMC_Exclude_Posts_From_River::get_instance();
}

pmc_exclude_posts_from_river_loader();


//EOF