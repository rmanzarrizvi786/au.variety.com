<?php
/**
 * Plugin Name: PMC Gallery V4
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
 * Version: 4.0.0
 * Author: PMC,
 * License: PMC Proprietary.  All rights reserved.
 *
 * @package pmc-plugins
 */

if ( ! defined( 'PMC_GALLERY_VERSION' ) ) {
	define( 'PMC_GALLERY_VERSION', '2022.1' );
}

if ( ! defined( 'PMC_GALLERY_PLUGIN_URL' ) ) {
	define( 'PMC_GALLERY_PLUGIN_URL', trailingslashit( plugins_url( null, __FILE__ ) ) );
}

if ( ! defined( 'PMC_GALLERY_PLUGIN_DIR' ) ) {
	define( 'PMC_GALLERY_PLUGIN_DIR', __DIR__ );
}

// Note: update this constant to force all gallery related cache to expire when code is deployed to production
define( 'PMC_GALLERY_CACHE_VERSION', '2022.1' );

// Load plugin dependencies
require_once __DIR__ . '/dependencies.php';

require_once __DIR__ . '/classes/class-settings.php';
PMC\Gallery\Settings::get_instance();

require_once __DIR__ . '/classes/class-defaults.php';
PMC\Gallery\Defaults::get_instance();

require_once __DIR__ . '/classes/class-attachment-detail.php';
PMC\Gallery\Attachment_Detail::get_instance();

require_once __DIR__ . '/classes/class-lists-settings.php';
PMC\Gallery\Lists_Settings::get_instance();

require_once __DIR__ . '/classes/class-lists.php';
PMC\Gallery\Lists::get_instance();

require_once __DIR__ . '/classes/class-attachment-image-credit.php';
PMC\Gallery\Attachment_Image_Credit::get_instance();

require_once __DIR__ . '/classes/services/apple-music/class-api-auth.php';
PMC\Gallery\Services\Apple_Music\API_Auth::get_instance();

if ( is_admin() ) {

	require_once __DIR__ . '/classes/admin/class-link-content.php';
	PMC\Gallery\Admin\Link_Content::get_instance();

	require_once __DIR__ . '/classes/admin/class-related-galleries.php';
	PMC\Gallery\Admin\Related_Galleries::get_instance();

	require_once __DIR__ . '/classes/admin/class-media-manager.php';
	PMC\Gallery\Admin\Media_Manager::get_instance();
}

require_once __DIR__ . '/classes/class-instant-articles.php';
PMC\Gallery\Instant_Articles::get_instance();

require_once __DIR__ . '/classes/class-pmc-store-products.php';
PMC\Gallery\PMC_Store_Products::get_instance();

require_once __DIR__ . '/classes/class-view-legacy.php';

if ( ! is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once __DIR__ . '/classes/class-link-content-view.php';
	PMC\Gallery\Link_Content_View::get_instance();

	require_once __DIR__ . '/classes/class-view.php';
	PMC\Gallery\View::get_instance();
}

require_once __DIR__ . '/classes/class-attachment-taxonomy.php';
PMC\Gallery\Attachment_Taxonomy::get_instance();

require_once __DIR__ . '/classes/class-rest-apis.php';
PMC\Gallery\Rest_APIs::get_instance();

require_once __DIR__ . '/classes/class-plugin.php';
PMC\Gallery\Plugin::get_instance();

// Backwards compat.
require __DIR__ . '/compatibility.php';

// load pmc-comscore plugin.
pmc_load_plugin( 'pmc-comscore', 'pmc-plugins' );
