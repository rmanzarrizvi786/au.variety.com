<?php
/**
 * Plugin Name: PMC Gallery
 * Description:
 *  - Allows to create a customized gallery.
 *  - Allows to show customized gallery Preview.
 *  - The ability to "return to article" on the gallery page
 *  - Timer countdown interstitial ad with custom duration and clicks..
 *  - Rotate Ads with custom clicks.
 *  - Touch(swipe) and Keyboard(arrow) Movements.
 *  - New Media UI and old method both accepted
 *  - Maintains AJAX history
 *  - Allows direct image linking
 *  - Drawback : Single Gallery
 * Version: 3.0.0
 * Author: PMC,
 * License: PMC Proprietary.  All rights reserved.
 *
 * @package pmc-plugins
 * @todo Remove hollywoodlife_image_credit() when this goes live
 */

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
if ( ! defined( 'PMC_GALLERY_PLUGIN_URL' ) ) {
	define( 'PMC_GALLERY_PLUGIN_URL', trailingslashit( plugins_url( null, __FILE__ ) ) );
}

require __DIR__ . '/class/class-pmc-gallery-settings.php';
require __DIR__ . '/class/class-pmc-gallery-defaults.php';
require __DIR__ . '/class/class-pmc-gallery-attachment-detail.php';
require __DIR__ . '/compatibility.php'; // allows pmc-gallery to be backward compatible.

if ( is_admin() ) {
	require __DIR__ . '/class/class-pmc-gallery-image-credit-admin.php';
	require __DIR__ . '/class/class-pmc-gallery-link-content-admin.php';
	require __DIR__ . '/class/class-pmc-gallery-admin-media-manager.php';
}

require __DIR__ . '/class/class-pmc-gallery-instant-articles.php';

require_once __DIR__ . '/class/class-pmc-store-products.php';
PMC\Gallery\PMC_Store_Products::get_instance();

if ( ! is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require __DIR__ . '/class/class-pmc-gallery-link-content-view.php';
	require __DIR__ . '/class/class-pmc-gallery-view.php';
}

require( __DIR__ . '/class/class-pmc-gallery-attachment-taxonomy.php' );
// load pmc-comscore plugin.
pmc_load_plugin( 'pmc-comscore', 'pmc-plugins' );
