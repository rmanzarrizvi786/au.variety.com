<?php
/**
 * Plugin Name: PMC Google Amp
 * Description: Adds support for google AMP mobile pages
 * Version: 1.0
 * Author: PMC, Brandon Camenisch <bcamenisch@pmc.com>
 * License: PMC Proprietary.  All rights reserved
 * Text Domain: pmc-google-amp
 * Domain Path: /languages
 */

use PMC\Google_Amp\Plugin;

define( 'PMC_GOOGLE_AMP_ROOT', __DIR__ );

define( 'PMC_GOOGLE_AMP_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
wpcom_vip_load_plugin( 'pmc-google-universal-analytics', 'pmc-plugins' );

function pmc_google_amp_loader() {

	\PMC\Google_Amp\Single_Post::get_instance();

	if ( defined( 'ENABLE_AMP_GALLERY' ) && ENABLE_AMP_GALLERY ) {
		\PMC\Google_Amp\Single_PMC_Gallery::get_instance();
	}

	\PMC\Google_Amp\Analytics\Chartbeat::get_instance();

	\PMC\Google_Amp\Optimera::get_instance();

}

pmc_google_amp_loader();

Plugin::get_instance();
