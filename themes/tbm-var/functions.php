<?php

/**
 * Child theme initialization file.
 *
 * @package pmc-variety
 *
 * @since   2019-08-13
 */

define('CHILD_THEME_PATH', get_stylesheet_directory());
define('CHILD_THEME_URL', get_stylesheet_directory_uri());

define('PMC_CORE_PATH', CHILD_THEME_PATH . '/pmc');

define('PMC_SPARK_THEME_VERSION', '2021.6');
define('PMC_LARVA', true);
define('PMC_CORE_PERMALINK_DISABLE', true);
define('PMC_PLUGINS_DIR', WP_PLUGIN_DIR . '/pmc-plugins/');

if (isset($_ENV) && isset($_ENV['ENVIRONMENT']) && 'sandbox' == $_ENV['ENVIRONMENT']) {
	define('CDN_URL', CHILD_THEME_URL . '/assets/build/');
} else {
	define('CDN_URL', 'https://cdn.thebrag.com/var/');
}


if (!defined('PMC_SITE_NAME')) {
	define('PMC_SITE_NAME', 'variety');
}

if (!defined('PMC_ENABLE_LEAN_CONTENT_FOR_GOOGLEBOT')) {
	define('PMC_ENABLE_LEAN_CONTENT_FOR_GOOGLEBOT', true);
}

if (!defined('USE_SHARED_CAROUSEL_PLUGIN')) {
	// Flag to use custom URL in featured carousel posts.
	define('USE_SHARED_CAROUSEL_PLUGIN', true);
}

if (!defined('PMC_ADMIN_GA_ACCOUNT_ID')) {
	define('PMC_ADMIN_GA_ACCOUNT_ID', 'UA-3370317-4');
}

/*
 * Only load vip-init.php on VIP Classic
 */
if (!defined('VIP_GO_APP_ENVIRONMENT') || false === VIP_GO_APP_ENVIRONMENT) {

	// Init WP.com VIP environment
	$_vip_init_file = WP_CONTENT_DIR . '/themes/vip/plugins/vip-init.php';

	if (file_exists($_vip_init_file) && validate_file($_vip_init_file) === 0) {
		require_once $_vip_init_file; // phpcs:ignore
	}
} else {

	/*
	 * VIP Init file will not be loaded by theme on VIP Go,
	 * it will be loaded by mu-plugins. So we don't have to worry about it here.
	 */

	// workaround as some of the pmc plugins are calling deprecated function calls in the vip go mu plugins
	add_filter('deprecated_function_trigger_error', '__return_false');
}

/**
 * Load up the class autoloader
 */

require_once(WP_PLUGIN_DIR . '/cheezcap/cheezcap.php');


// require_once PMC_CORE_PATH . '/pmc/inc/helpers/autoloader.php';
require_once PMC_CORE_PATH . '/inc/helpers/gallery-helpers.php';

require_once PMC_PLUGINS_DIR . '/pmc-global-functions/pmc-global-functions.php';
require_once PMC_PLUGINS_DIR . '/pmc-global-functions/classes/traits/trait-singleton.php';

require_once CHILD_THEME_PATH . '/inc/helpers/autoloader.php';

require_once PMC_PLUGINS_DIR . '/pmc-larva/pmc-larva.php';
require_once PMC_PLUGINS_DIR . '/pmc-vertical/pmc-vertical.php';
require_once PMC_PLUGINS_DIR . '/pmc-ajax-pagination/pmc-ajax-pagination.php';
require_once PMC_PLUGINS_DIR . '/fm-widgets/fm-widgets.php';
require_once PMC_PLUGINS_DIR . '/ig-custom-metaboxes/ig-custom-metaboxes.php';
require_once PMC_PLUGINS_DIR . '/pmc-options/pmc-options.php';
require_once PMC_PLUGINS_DIR . '/pmc-primary-taxonomy/pmc-primary-taxonomy.php';
require_once PMC_PLUGINS_DIR . '/pmc-social-share-bar/pmc-social-share-bar.php';
require_once PMC_PLUGINS_DIR . '/pmc-gallery-v4/pmc-gallery-v4.php';
// require_once PMC_PLUGINS_DIR . '/pmc-subscription-v2/pmc-subscription-v2.php';
require_once PMC_PLUGINS_DIR . '/pmc-field-overrides/pmc-field-overrides.php';
require_once PMC_PLUGINS_DIR . '/pmc-featured-video-override/pmc-featured-video-override.php';
require_once PMC_PLUGINS_DIR . '/pmc-linkcontent/pmc-linkcontent.php';
require_once PMC_PLUGINS_DIR . '/pmc-post-options/pmc-post-options.php';
require_once PMC_PLUGINS_DIR . '/pmc-js-libraries/pmc-js-libraries.php';
require_once PMC_PLUGINS_DIR . '/pmc-structured-data/pmc-structured-data.php';
require_once PMC_PLUGINS_DIR . '/pmc-carousel/pmc-carousel.php';
require_once PMC_PLUGINS_DIR . '/pmc-guest-authors/pmc-guest-authors.php';


// require_once PMC_PLUGINS_DIR . '/pmc-geo-uniques/pmc-geo-uniques.php';
// require_once PMC_PLUGINS_DIR . '/pmc-adm-v2/pmc-adm-v2.php';

require_once PMC_PLUGINS_DIR . '/pmc-lists/pmc-lists.php';

require_once PMC_CORE_PATH . '/inc/classes/class-admin.php';
require_once PMC_CORE_PATH . '/inc/classes/class-theme.php';
require_once PMC_CORE_PATH . '/inc/classes/class-larva.php';
require_once PMC_CORE_PATH . '/inc/classes/class-injection.php';
require_once PMC_CORE_PATH . '/inc/classes/fieldmanager/class-fields.php';
require_once PMC_CORE_PATH . '/inc/classes/class-assets.php';
require_once PMC_CORE_PATH . '/inc/classes/class-menu.php';
require_once PMC_CORE_PATH . '/inc/classes/class-media.php';
require_once PMC_CORE_PATH . '/inc/classes/class-author.php';
require_once PMC_CORE_PATH . '/inc/classes/meta/class-byline.php';
require_once PMC_CORE_PATH . '/inc/classes/class-sharing.php';
require_once PMC_CORE_PATH . '/inc/classes/class-carousels.php';
require_once PMC_CORE_PATH . '/inc/classes/class-helper.php';
require_once PMC_CORE_PATH . '/inc/classes/class-top-posts.php';

require_once PMC_CORE_PATH . '/plugins/filter-posts/filter-posts.php';

// require_once PMC_CORE_PATH . '/inc/classes/widgets/class-global-curateable.php';
// require_once PMC_CORE_PATH . '/inc/classes/widgets/class-social-profiles.php';

\PMC\Core\Inc\Theme::get_instance();

/**
 * Theme Init
 *
 * Sets up the theme.
 *
 * @since 2018-12-18
 */
function variety_init()
{
	\Variety\Inc\Variety::get_instance();
}

add_action('after_setup_theme', 'variety_init', 1);

/**
 * Use for social share twitter icon.
 * Ex. like @variety.
 */
if (!defined('PMC_TWITTER_SITE_USERNAME')) {
	define('PMC_TWITTER_SITE_USERNAME', 'variety');
}

// Enable site map on demand generation if rebuild is required.
define('PMC_SITEMAP_REBUILD_ON_DEMAND', 1);

add_filter('send_password_change_email', '__return_false');

/**
 * WP-CLI commands.
 *
 */
if (defined('WP_CLI') && WP_CLI && file_exists(CHILD_THEME_PATH . '/wp-cli/init.php')) {
	require_once CHILD_THEME_PATH . '/wp-cli/init.php';
}


/**
 * Loads a plugin out of our shared plugins directory.
 *
 * @link http://lobby.vip.wordpress.com/plugins/ VIP Shared Plugins
 * @param string $plugin Optional. Plugin folder name (and filename) of the plugin
 * @param string $folder Optional. Folder to include from; defaults to "plugins". Useful for when you have multiple themes and your own shared plugins folder.
 * @param string|bool $version Optional. Specify which version of the plugin to load. Version should be in the format 1.0.0. Passing true triggers legacy release candidate support.
 *
 * @return bool True if the include was successful
 */
function wpcom_vip_load_plugin($plugin = false, $folder = 'plugins', $version = false)
{
	static $loaded_plugin_slugs = array();

	// Make sure there's a plugin to load
	if (empty($plugin)) {
		// On WordPress.com, use an internal function to message VIP about a bad call to this function
		if (function_exists('wpcom_is_vip')) {
			if (function_exists('send_vip_team_debug_message')) {
				// Use an expiring cache value to avoid spamming messages
				if (!wp_cache_get('noplugin', 'wpcom_vip_load_plugin')) {
					send_vip_team_debug_message('WARNING: wpcom_vip_load_plugin() is being called without a $plugin parameter', 1);
					wp_cache_set('noplugin', 1, 'wpcom_vip_load_plugin', 3600);
				}
			}
			return false;
		} else {
			die('wpcom_vip_load_plugin() was called without a first parameter!');
		}
	}

	$plugin_slug = $plugin; // Unversioned plugin name

	// Get the version number, if we have one
	if (is_string($version) && false !== $plugin) {
		$plugin = $plugin . '-' . $version; // Versioned plugin name
	}

	// Liveblog is a special flower. We need to check this theme/site can use it
	// Skip if we're loading 1.3, as that's loaded by vip-friends.php
	// $plugin will include a version number by this point if it's above 1.3
	if ('liveblog' == $plugin_slug && 'liveblog' != $plugin) {

		if (function_exists('wpcom_vip_is_liveblog_enabled') && !wpcom_vip_is_liveblog_enabled()) {
			// For now, we'll just bail.
			// @todo Log to IRC
			return false;
		}
	}

	// Prevent double-loading of different versions of the same plugin.
	$local_plugin_key = sprintf('%s__%s', $folder, $plugin_slug);
	if (isset($loaded_plugin_slugs[$local_plugin_key])) {
		// TODO: send alert when `$loaded_plugin_slugs[ $local_plugin_key ] !== $version`

		return false;
	}
	$loaded_plugin_slugs[$local_plugin_key] = $version;

	// Find the plugin
	$plugin_locations = _wpcom_vip_load_plugin_get_locations($folder, $version);
	$include_path = _wpcom_vip_load_plugin_get_include_path($plugin_locations, $plugin, $plugin_slug);

	// Reset the folder based on where the plugin actually lives, and get the full path for inclusion
	if (is_array($include_path)) {
		$folder = $include_path['folder'];
		$include_path = $include_path['full_path'];
	}

	// Now check we have an include path and include the plugin
	if (false !== $include_path) {

		wpcom_vip_add_loaded_plugin("$folder/$plugin");

		// Since we're going to be include()'ing inside of a function,
		// we need to do some hackery to get the variable scope we want.
		// See http://www.php.net/manual/en/language.variables.scope.php#91982

		// Start by marking down the currently defined variables (so we can exclude them later)
		$pre_include_variables = get_defined_vars();

		// Now include
		include_once($include_path);

		// If there's a wpcom-helper file for the plugin, load that too
		$helper_path = WP_CONTENT_DIR . "/themes/vip/$folder/$plugin/wpcom-helper.php";

		if (file_exists($helper_path)) {
			require_once($helper_path);
		}

		// Blacklist out some variables
		$blacklist = array('blacklist' => 0, 'pre_include_variables' => 0, 'new_variables' => 0);

		// Let's find out what's new by comparing the current variables to the previous ones
		$new_variables = array_diff_key(get_defined_vars(), $GLOBALS, $blacklist, $pre_include_variables);

		// global each new variable
		foreach ($new_variables as $new_variable => $devnull)
			global ${$new_variable};

		// Set the values again on those new globals
		extract($new_variables);

		return true;
	} else {
		// On WordPress.com, use an internal function to message VIP about the bad call to this function
		// if (function_exists('wpcom_is_vip')) {
		if (1) {
			if (function_exists('send_vip_team_debug_message')) {
				// Use an expiring cache value to avoid spamming messages
				$cachekey = md5($folder . '|' . $plugin);
				if (!wp_cache_get("notfound_$cachekey", 'wpcom_vip_load_plugin')) {
					send_vip_team_debug_message("WARNING: wpcom_vip_load_plugin() is trying to load a non-existent file ( /$folder/$plugin/$plugin_slug.php )", 1);
					wp_cache_set("notfound_$cachekey", 1, 'wpcom_vip_load_plugin', 3600);
				}
			}
			return false;

			// die() in non-WordPress.com environments so you know you made a mistake
		} else {
			die("Unable to load $plugin ({$folder}) using wpcom_vip_load_plugin()!");
		}
	}
}

/**
 * Get a list of possible plugin locations.
 *
 * Given the details passed to wpcom_vip_load_plugin(), figure out where the plugin could reside and pass that back.
 *
 * @param string $folder The folder we should be looking for
 * @param int $version A version number
 *
 * @return array Returns an array of possible plugin locations
 */
function _wpcom_vip_load_plugin_get_locations($folder, $version)
{

	// Make a list of possible plugin locations
	$plugin_locations = [];

	// Allow VIPs to load plugins bundled in their theme
	if ('theme' === $folder) {
		$theme = wp_get_theme();

		// Add the child theme to paths array, if applicable
		if ($theme->get_stylesheet() !== $theme->get_template()) {
			// Convert "vip/[theme-name]" to "[theme-name]/plugins"
			$plugin_locations[] = str_replace('vip/', '', $theme->get_stylesheet()) . '/plugins';
		}

		// Always check the "parent" (which may just be the active theme)
		// and convert "vip/[theme-name]" to "[theme-name]/plugins"
		$plugin_locations[] = str_replace('vip/', '', $theme->get_template()) . '/plugins';
	}

	// Provide backwards-compatibility for release candidates
	if (true === $version) {
		$plugin_locations[] = $folder . '/release-candidates';
	}

	// Always look for plugins in the standard plugins dir/shared plugins repos
	$plugin_locations[] = $folder;

	return $plugin_locations;
}

/**
 * Determine the full include path to the plugin.
 *
 * Gathers all the various bits, puts them together and checks that the plugin path is valid.
 *
 * @param array $plugin_locations A list of possible locations from _wpcom_vip_load_plugin_get_locations()
 * @param string $plugin The versioned plugin name
 * @param string $plugin_slug The unversioned plugin slug
 *
 * @return array|bool An array with the full path, and folder part or false if no valid path was found
 */
function _wpcom_vip_load_plugin_get_include_path($plugin_locations = [], $plugin, $plugin_slug)
{

	// Check each possible location, using the first gives a usable plugin path
	foreach ($plugin_locations as $plugin_location) {
		$path_to_check = sprintf(
			'%s/themes/vip/%s/%s/%s.php',
			WP_CONTENT_DIR,
			$plugin_location,
			_wpcom_vip_load_plugin_sanitizer($plugin),
			_wpcom_vip_load_plugin_sanitizer($plugin_slug)
		);

		if (file_exists($path_to_check)) { // We've found a valid plugin path
			// We need to return the full path for the include, but also the location which is used
			// elsewhere to check what plugins are active on a site.
			return [
				'full_path' => $path_to_check,
				'folder' => $plugin_location,
			];
		}
	}

	// If we don't find the plugin anywhere, return false
	return false;
}

/**
 * Helper function for wpcom_vip_load_plugin(); sanitizes plugin folder name.
 *
 * You shouldn't use this function.
 *
 * @param string $folder Folder name
 * @return string Sanitized folder name
 */
function _wpcom_vip_load_plugin_sanitizer($folder)
{
	$folder = preg_replace('#([^a-zA-Z0-9-_.]+)#', '', $folder);
	$folder = str_replace('..', '', $folder); // To prevent going up directories

	return $folder;
}

function pmc_adm_render_ads($ad_location, $ad_title = '', $echo = true, $provider = '')
{
	if (!class_exists('\TBM\TBMAds'))
		return;
	$ads = \TBM\TBMAds::get_instance();
	echo $ads->get_ad($ad_location);
	return;
}

/**
 * Return the directory path for a given VIP theme
 *
 * @link http://vip.wordpress.com/documentation/mobile-theme/ Developing for Mobile Phones and Tablets
 * @param string $theme Optional. Name of the theme folder
 * @return string Path for the specified theme
 */
function wpcom_vip_theme_dir($theme = '')
{
	if (empty($theme))
		$theme = get_stylesheet();

	// Simple sanity check, in case we get passed a lame path
	$theme = ltrim($theme, '/');
	$theme = str_replace('vip/', '', $theme);

	return sprintf('%s/themes/vip/%s', WP_CONTENT_DIR, $theme);
}

function brands()
{
	$pub_logos = [
		'the-brag' => [
			'title' => 'The Brag',
			'link' => 'https://thebrag.com/',
			'logo_name' => 'The-Brag_combo',
			'ext' => 'svg',
		],
		'brag-jobs' => [
			'title' => 'The Brag Jobs',
			'link' => 'https://thebrag.com/jobs',
			'logo_name' => 'The-Brag-Jobs',
			'width' => 80,
			'ext' => 'png',
		],
		'dbu' => [
			'title' => 'Don\'t Bore Us',
			'link' => 'https://dontboreus.thebrag.com/',
			'logo_name' => 'Dont-Bore-Us',
			'ext' => 'svg',
		],
		'tio' => [
			'title' => 'The Industry Observer',
			'link' => 'https://theindustryobserver.thebrag.com/',
			'logo_name' => 'The-Industry-Observer',
			'ext' => 'svg',
		],
		'rolling-stone-australia' => [
			'title' => 'Rolling Stone Australia',
			'link' => 'https://au.rollingstone.com/',
			'logo_name' => 'Rolling-Stone-Australia',
			'ext' => 'png',
		],
		'tone-deaf' => [
			'title' => 'Tone Deaf',
			'link' => 'https://tonedeaf.thebrag.com/',
			'logo_name' => 'Tone-Deaf',
			'ext' => 'svg',
			'width' => 80
		],
		'tmn' => [
			'title' => 'The Music Network',
			'link' => 'https://themusicnetwork.com/',
			'logo_name' => 'TMN',
			'ext' => 'svg',
			'width' => 80
		],
	];
	return $pub_logos;
} // brands()

function brands_network()
{
	$pub_logos = [
		/**
		 * EPIC
		 */
		'lwa' => [
			'title' => 'Life Without Andy',
			'link' => 'https://lifewithoutandy.com/',
			'logo_name' => 'lwa',
			'ext' => 'png',
			'width' => 60
		],
		'hypebeast' => [
			'title' => 'Hypebeast',
			'link' => 'https://hypebeast.com/',
			'logo_name' => 'Hypebeast',
			'ext' => 'png',
		],
		'funimation' => [
			'title' => 'Funimation',
			'link' => 'https://www.funimation.com/',
			'logo_name' => 'Funimation',
			'ext' => 'png',
		],
		'crunchyroll' => [
			'title' => 'Crunchyroll',
			'link' => 'https://www.crunchyroll.com/en-gb',
			'logo_name' => 'Crunchyroll',
			'ext' => 'png',
		],
		'enthusiast' => [
			'title' => 'Enthusiast Gaming',
			'link' => 'https://www.enthusiastgaming.com/',
			'logo_name' => 'enthusiast',
			'ext' => 'png',
		],
		'gamelancer' => [
			'title' => 'Gamelancer',
			'link' => 'https://gamelancer.com/',
			'logo_name' => 'Gamelancer',
			'ext' => 'png',
		],

		/**
		 * PMC
		 */
		'artnews' => [
			'title' => 'ARTnews',
			'link' => 'https://www.artnews.com/',
			'logo_name' => 'ARTnews',
		],
		'bgr' => [
			'title' => 'BGR',
			'link' => 'https://bgr.com/',
			'logo_name' => 'bgr',
			'width' => 80
		],
		'billboard' => [
			'title' => 'Billboard',
			'link' => 'https://billboard.com/',
			'logo_name' => 'billboard',
		],
		'deadline' => [
			'title' => 'Deadline',
			'link' => 'https://deadline.com/',
			'logo_name' => 'DEADLINE',
		],
		'dirt' => [
			'title' => 'Dirt',
			'link' => 'https://www.dirt.com/',
			'logo_name' => 'Dirt',
			'width' => 80
		],
		'footwear' => [
			'title' => 'Footwear News',
			'link' => 'https://footwearnews.com/',
			'logo_name' => 'FootwearNews',
			'width' => 60
		],
		'gold-derby' => [
			'title' => 'Gold Derby',
			'link' => 'https://www.goldderby.com/',
			'logo_name' => 'GoldDerby',
		],
		'indiewire' => [
			'title' => 'IndieWire',
			'link' => 'https://www.indiewire.com/',
			'logo_name' => 'IndieWire',
		],
		'sheknows' => [
			'title' => 'SheKnows',
			'link' => 'https://www.sheknows.com/',
			'logo_name' => 'SheKnows',
		],
		'sourcing-journal' => [
			'title' => 'Sourcing Journal',
			'link' => 'https://sourcingjournal.com/',
			'logo_name' => 'SourcingJournal',
		],
		'sportico' => [
			'title' => 'Sportico',
			'link' => 'https://www.sportico.com/',
			'logo_name' => 'Sportico',
		],
		'spy' => [
			'title' => 'Spy',
			'link' => 'https://spy.com/',
			'logo_name' => 'Spy',
			'width' => 120,
		],
		'stylecaster' => [
			'title' => 'Stylecaster',
			'link' => 'https://stylecaster.com/',
			'logo_name' => 'Stylecaster',
		],
		'the-hollywood-reporter' => [
			'title' => 'The Hollywood Reporter',
			'link' => 'https://www.hollywoodreporter.com/',
			'logo_name' => 'The-Hollywood-Reporter',
		],
		'tvline' => [
			'title' => 'TVLine',
			'link' => 'https://tvline.com/',
			'logo_name' => 'TVLine',
			'width' => 120,
		],
		'variety' => [
			'title' => 'Variety',
			'link' => 'https://variety.com/',
			'logo_name' => 'Variety',
			'width' => 120,
		],
		'vibe' => [
			'title' => 'VIBE',
			'link' => 'https://www.vibe.com/',
			'logo_name' => 'Vibe',
			'width' => 120,
		],
	];
	return $pub_logos;
} // brands_network()