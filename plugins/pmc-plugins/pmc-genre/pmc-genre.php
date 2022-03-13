<?php
/*
Plugin Name: PMC Genre
Plugin URI: http://www.pmc.com
Description: Adds the Genre parent taxonomy that allows better categorization of articles.
Version: 0.1
Author: PMC, Amit Gupta
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_GENRE_ROOT', __DIR__ );
define( 'PMC_GENRE_VERSION', '0.1' );

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

function pmc_genre_loader() {
	/*
	 * Initialize PMC Genre Taxonomy
	 */
	PMC\Genre\Taxonomy::get_instance();

	if ( is_admin() ) {
		/*
		 * Load up and initialize wp-admin related functionality
		 */
		/*
		 * Disabled settings page and filtering on post screen temporarily.
		 * Some changes needed in way relations are mapped before this can
		 * be enabled back again.
		 *
		 * @since 2015-03-25 Amit Gupta

		PMC\Genre\Settings::get_instance();
		PMC\Genre\Post_UI::get_instance();

		 */
	} else {
		/*
		 * Load up and initialize front-end related functionality
		 */
		PMC\Genre\Frontend::get_instance();
	}
}

pmc_genre_loader();


//EOF