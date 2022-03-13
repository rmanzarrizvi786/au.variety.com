<?php
/**
 * Plugin Name:     PMC Mobile API
 * Plugin URI:      https://bitbucket.org/penskemediacorp/pmc-plugins/
 * Description:     Mobile Apps API base for PMC sites
 * Author:          Alley
 * Author URI:      https://alley.co/
 * Text Domain:     pmc-mobile-api
 * Version:         0.1.0
 *
 * @package         PMC_Mobile_API
 */

namespace PMC\Mobile_API;

use PMC\Mobile_API\Endpoints\Article;
use PMC\Mobile_API\Endpoints\Breaking_News;
use PMC\Mobile_API\Endpoints\Config;
use PMC\Mobile_API\Endpoints\Gallery;
use PMC\Mobile_API\Endpoints\Home;
use PMC\Mobile_API\Endpoints\Latest_News;
use PMC\Mobile_API\Endpoints\Latest_Video;
use PMC\Mobile_API\Endpoints\Lists;
use PMC\Mobile_API\Endpoints\Menu;
use PMC\Mobile_API\Endpoints\Personal_Feed;
use PMC\Mobile_API\Endpoints\Personalization;
use PMC\Mobile_API\Endpoints\Section_Front;
use PMC\Mobile_API\Endpoints\Video_Front;
use PMC\Mobile_API\Endpoints\Video_Playlist;
use PMC\Mobile_API\Endpoints\Latest_Lists;

// Plugin autoloader.
require_once __DIR__ . '/src/autoload.php';

add_action( 'after_setup_theme', __NAMESPACE__ . '\loader' );
add_action( 'pmc_mobile_api_add_routes', __NAMESPACE__ . '\core_routes' );

/**
 * Initialize the mobile API.
 */
function loader() {
	$menus = new Menus();
	$menus->register_menus();

	$registrar = new Route_Registrar();
	add_action( 'rest_api_init', [ $registrar, 'register_routes' ] );

	/**
	 * This action fires once the registrar has loaded, before routes are added.
	 *
	 * @param Route_Registrar $registrar Route registrar.
	 */
	do_action( 'pmc_mobile_api_load_registrar', $registrar );

	/**
	 * Add routes to the registrar.
	 *
	 * @param Route_Registrar $registrar Route registrar.
	 */
	do_action( 'pmc_mobile_api_add_routes', $registrar );

	/**
	 * This action fires once the plugin has initialized.
	 *
	 * @param Route_Registrar $registrar Route registrar.
	 */
	do_action( 'pmc_mobile_api_init', $registrar );
}

/**
 * Register the core routes for the PMC Mobile API plugin.
 *
 * @param Route_Registrar $registrar Global route registrar.
 */
function core_routes( Route_Registrar $registrar ) {
	$registrar->add_endpoint( '/home', new Home() );
	$registrar->add_endpoint( '/latest-news', new Latest_News() );
	$registrar->add_endpoint( '/menu/main', new Menu() );
	$registrar->add_endpoint( '/section/vertical/(?P<id>\d+)', new Section_Front( 'vertical' ) );
	$registrar->add_endpoint( '/section/category/(?P<id>\d+)', new Section_Front( 'category' ) );
	$registrar->add_endpoint( '/section/post_tag/(?P<id>\d+)', new Section_Front( 'post_tag' ) );
	$registrar->add_endpoint( '/article/(?P<id>\d+)', new Article() );
	$registrar->add_endpoint( '/gallery/(?P<id>\d+)', new Gallery() );
	$registrar->add_endpoint( '/list/(?P<id>\d+)', new Lists() );
	$registrar->add_endpoint( '/video', new Video_Front() );
	$registrar->add_endpoint( '/video/latest', new Latest_Video() );
	$registrar->add_endpoint( '/video/latest/(?P<category>[^/]+)', new Latest_Video() );
	$registrar->add_endpoint( '/list/latest', new Latest_Lists() );
	$registrar->add_endpoint( '/video/playlist', new Video_Playlist() );
	$registrar->add_endpoint( '/breaking-news', new Breaking_News() );
	$registrar->add_endpoint( '/config', new Config() );
	$registrar->add_endpoint( '/personalization', new Personalization() );
	$registrar->add_endpoint( '/personal-feed', new Personal_Feed() );
}
