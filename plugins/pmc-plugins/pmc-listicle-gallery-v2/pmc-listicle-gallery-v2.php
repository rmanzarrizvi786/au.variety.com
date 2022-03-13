<?php
/*
Plugin Name: PMC Listicle Gallery v2
Description: Defines a listicle gallery containing one or more image carousels
Version: 2.0.0
Author: Fardin Pakravan
Author URI: http://www.pmc.com
Author Email: fpakravan@pmc.com
License: PMC Proprietary. All rights reserved.
*/

if ( ! defined ( 'LISTICLE_GALLERY_V2_ASSETS_URL' ) ) {
	define( 'LISTICLE_GALLERY_V2_ASSETS_URL', plugin_dir_url( __FILE__ ) . '/assets' );
}

if ( ! defined ( 'LISTICLE_GALLERY_V2_ROOT_DIR' ) ) {
	define( 'LISTICLE_GALLERY_V2_ROOT_DIR', __DIR__ );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'pmc-listicle-gallery', 'PMC\Listicle_Gallery_V2\Gallery_CLI' );
}

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
pmc_load_plugin( 'fieldmanager', false, '1.1' );

function pmc_listicle_gallery_v2_loader() {

	\PMC\Listicle_Gallery_V2\Services\Gallery::get_instance();
	\PMC\Listicle_Gallery_V2\Services\Gallery_Item::get_instance();

}

pmc_listicle_gallery_v2_loader();
