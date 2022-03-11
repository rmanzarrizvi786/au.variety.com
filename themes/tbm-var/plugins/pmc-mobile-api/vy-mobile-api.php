<?php
/**
 * Plugin Name:     Variety Mobile API
 * Plugin URI:      https://bitbucket.org/penskemediacorp/pmc-variety-2020/
 * Description:     Mobile Apps API extension for Variety
 * Author:          PMC
 * Text Domain:     variety-mobile-api
 * Version:         1.0
 *
 * @package         Variety_Mobile_API
 */

namespace PMC\Variety\Mobile_API;

use PMC\Mobile_API\Endpoints\Section_Front;
use PMC\Mobile_API\Route_Registrar;
use PMC\VY\Mobile_API\Endpoints\VY_Article;
use PMC\VY\Mobile_API\Endpoints\VY_Gallery;
use PMC\VY\Mobile_API\Endpoints\VY_Latest_News;
use PMC\VY\Mobile_API\Endpoints\VY_Video_Playlists;
use PMC\VY\Mobile_API\Endpoints\VY_VIP_Commentary;
use PMC\VY\Mobile_API\Endpoints\VY_VIP_Videos;
use PMC\VY\Mobile_API\Endpoints\VY_Video_Landing;
use PMC\VY\Mobile_API\Endpoints\VY_Video_Latest;

// Plugin autoloader.
require_once __DIR__ . '/src/autoload.php';

// PMC_Mobile_API Configurations.
require_once __DIR__ . '/src/config.php';

add_action( 'pmc_mobile_api_add_routes', __NAMESPACE__ . '\vy_routes' );

/**
 * Register the core routes for the PMC Mobile API plugin.
 *
 * @param Route_Registrar $registrar Global route registrar.
 */
function vy_routes( Route_Registrar $registrar ) {
	$registrar->add_endpoint( '/latest-news', new VY_Latest_News() );
	$registrar->add_endpoint( '/article/(?P<id>\d+)', new VY_Article() );
	$registrar->add_endpoint( '/gallery/(?P<id>\d+)', new VY_Gallery() );
	$registrar->add_endpoint( '/vip-commentary', new VY_VIP_Commentary() );
	$registrar->add_endpoint( '/vip-videos', new VY_VIP_Videos() );

	// Video sections.
	$registrar->add_endpoint( '/section/vcategory/(?P<id>\d+)', new Section_Front( 'vcategory' ) );
	$registrar->add_endpoint( '/video/landing/', new VY_Video_Landing() );
	$registrar->add_endpoint( '/video/latest', new VY_Video_Latest() );
	$registrar->add_endpoint( '/video/playlists', new VY_Video_Playlists() );
}
