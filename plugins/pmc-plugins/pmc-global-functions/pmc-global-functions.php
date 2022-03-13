<?php
/*
Plugin Name: PMC Library Functions
Description: Library Functions for our various plugins.	 This should only contain very generic functions.  If you include site specific functions here we'll kill you.
Version: 1.2
License: PMC Proprietary.  All rights reserved.
*/

// phpcs:disable Squiz.PHP.CommentedOutCode.Found

define( 'PMC_GLOBAL_VERSION', '2021.4' );

define( 'PMC_GLOBAL_FUNCTIONS_PATH', __DIR__ );
define( 'PMC_GLOBAL_FUNCTIONS_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

/**
 * If a site is not running on Go, explicitly declare PMC_IS_VIP_GO_SITE false.
 * Use the VIP_GO_ENV constant to detect Production Go sites.
 *
 * @since 2016-05-04 Corey Gilmore
 * @see https://wordpressvip.zendesk.com/hc/en-us/requests/52510
 *
 */
if ( ! defined( 'PMC_IS_VIP_GO_SITE' ) ) {
	// VIP_GO_ENV is set to the environment name (develop, staging, production) for Go sites,
	// and set to false for non-Go sites.
	if ( defined( 'VIP_GO_ENV' ) && false !== VIP_GO_ENV ) {
		define( 'PMC_IS_VIP_GO_SITE', true );
	} else {
		define( 'PMC_IS_VIP_GO_SITE', false );
	}
}

/**
 * Disabling Remote login as per VIP to solve blank page issue for logged in users
 * //https://wordpressvip.zendesk.com/hc/en-us/requests/93200?page=1
 *
 * @codeCoverageIgnoreStart
 */
if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
	if ( ! defined( 'WPCOM_DISABLE_ASYNC_REMOTE_LOGIN' ) ) {
		define( 'WPCOM_DISABLE_ASYNC_REMOTE_LOGIN', true );
	}
}
// @codeCoverageIgnoreEnd

/**
 * Helper function to load plugins between vip & vipgo and plugin no longer exist in vip
 * If constant PMC_LOAD_PLUGIN is defined & folder==false, plugin will load from pmc-plugins folder if exists
 * We need to define this function before anything else
 * @param  string $plugin  [description]
 * @param  string $folder  [description]
 * @param  string $version The version in VIP environment, does not apply in VIPGO
 * @return bool
 */
function pmc_load_plugin( $plugin, $folder = false, $version = false ) {
	global $pmc_loaded_plugins;
	global $vip_loaded_plugins;

	if ( ! isset( $pmc_loaded_plugins ) ) {
		$pmc_loaded_plugins = [];
	}

	if ( ! isset( $vip_loaded_plugins ) ) {
		$vip_loaded_plugins = array();
	}

	// allow disabling plugin via filter
	if ( apply_filters( 'pmc_do_not_load_plugin', false, $plugin, $folder, $version ) ) {
		return false;
	}

	$plugin_key = sprintf( '%s/%s', empty( $folder ) ? 'plugins' : $folder, $plugin );

	if ( isset( $pmc_loaded_plugins[ $plugin_key ] ) ) {
		return false;
	}

	$pmc_loaded_plugins[ $plugin_key ] = $version;
	$plugin_directory_name             = $plugin;

	// If a version is specified, $vip_loaded_plugins will
	// have the version in each plugin filename, e.g.
	// with the following, the entry in $vip_loaded_plugins
	// will be 'plugins/fieldmanager':
	// wpcom_vip_load_plugin( 'fieldmanager' );
	//
	// However, if you instead do:
	// wpcom_vip_load_plugin( 'fieldmanager', false, '1.1' );
	// the entry will be 'plugins/fieldmanager-1.1'
	if ( false !== $version ) {
		$plugin_directory_name .= '-' . $version;
	}

	// if folder is specified and has pmc- prefix, bypass auto lookup
	if ( $folder && preg_match( '/^pmc-/', $plugin ) ) {

		if ( defined( 'PMC_IS_VIP_GO_SITE' ) && PMC_IS_VIP_GO_SITE && ! empty( $version ) ) {
			/*
			 * The plugin loader function on VIP Go sites does not support versions
			 * like its counterpart on VIP sites. This is a workaround for VIP Go
			 * sites to pass off 'plugin-name-version/plugin-name.php' to the function
			 * instead of 'plugin-name-version' so that we can keep code sanity while
			 * still being able to load a specific plugin version on VIP Go sites.
			 */
			$plugin  = sprintf( '%s/%s.php', untrailingslashit( $plugin_directory_name ), trim( $plugin, '/' ) );
			$version = false;
		}

		return wpcom_vip_load_plugin( $plugin, $folder, $version );
	}

	// check if plugin already loaded, plugin name should be unique whether it's in vip or vipgo environment
	$folders_to_check = array( 'plugins', 'shared-plugins', 'pmc-plugins' );
	foreach( $folders_to_check as $item ) {
		if ( in_array( "{$item}/{$plugin_directory_name}", $vip_loaded_plugins ) ) {
			return false;
		}
	}

	// set default folder if not passed
	if ( empty( $folder ) ) {

		if ( defined( 'PMC_LOAD_PLUGIN' ) ) {
			if ( file_exists( dirname( __DIR__ ) . "/{$plugin_directory_name}" ) ) {
				$folder = 'pmc-plugins';
			}
		}

	}

	if ( defined( 'PMC_IS_VIP_GO_SITE' ) && PMC_IS_VIP_GO_SITE ) {

		// We're on VIP GO env, default plugins folder must not pass, this indicate wp plugins location: themes/plugins
		if ( 'plugins' === $folder ) {
			$folder = false;
		}

		if ( ! empty( $version ) && version_compare( $version, '0.0.1', '>=' ) ) {
			/*
			 * The plugin loader function on VIP Go sites does not support versions
			 * like its counterpart on VIP sites. This is a workaround for VIP Go
			 * sites to pass off 'plugin-name-version/plugin-name.php' to the function
			 * instead of 'plugin-name-version' so that we can keep code sanity while
			 * still being able to load a specific plugin version on VIP Go sites.
			 */
			$plugin = sprintf( '%s/%s.php', untrailingslashit( $plugin_directory_name ), trim( $plugin, '/' ) );
		}

	} else {

		// We're on VIP env, default folder must be 'plugins', this indicate VIP plugins location: themes/vip/plugins
		if ( empty( $folder ) ) {
			$folder = 'plugins';
		}

	}

	// catch all
	return wpcom_vip_load_plugin( $plugin, $folder, $version );
}

/**
 * @param mixed $namespace_class_or_callable Accept a namespace, class, or a callable object
 * @param string $plugin_or_path             Reserved for future use
 */
function pmc_init_plugin(
		$namespace_class_or_callable,
		string $plugin_or_path // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	) : void {
	if ( is_string( $namespace_class_or_callable ) ) {
		if ( class_exists( $namespace_class_or_callable ) ) {
			$namespace_class_or_callable = [ $namespace_class_or_callable, 'get_instance' ];
		} elseif ( function_exists( $namespace_class_or_callable . '\init_plugin' ) ) {
			$namespace_class_or_callable .= '\init_plugin';
		}
	}
	if ( is_callable( $namespace_class_or_callable ) ) {
		add_action( 'after_setup_theme', $namespace_class_or_callable );
	}
}

require_once PMC_GLOBAL_FUNCTIONS_PATH . '/classes/autoloader.php'; // Global class autoloader for PMC Plugins

require_once PMC_GLOBAL_FUNCTIONS_PATH . '/php/pmc-user-management.php'; // User management options
require_once PMC_GLOBAL_FUNCTIONS_PATH . '/php/pmc-wpadmin.php'; // Admin-specific hooks

require_once PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-must-have-plugins.php'; // Must have plugins loader

// need to include this class first so we can define constant PMC_GLOBAL_FUNCTIONS_URL to be ssl friendly
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-nonce.php';


/**
 * IMPORTANT NOTE: we can't define constant PMC_GLOBAL_FUNCTIONS_URL to use as quick URL reference
 * as it does not allow us to override with custom cdn via PMC_CDN class.
 *
 * Converting constant PMC_GLOBAL_FUNCTIONS_URL into function avoid constant define
 * Since: 2016-11-07 Hau Vong
 *
 * This  function return the full path url to the pmc global functions plugin
 *
 * @param  string $path Optional path to append to the url
 * @return string       The ssl friendly full url
 */
function pmc_global_functions_url( $path = '' ) {
	$url = PMC::ssl_friendly_url( plugins_url( '', __FILE__ ) );
	if ( !empty( $path ) && is_string( $path ) )  {
		$url = untrailingslashit( $url ) . '/' . PMC::unleadingslashit( $path );
	}
	return $url;
}

function __pmc_return_is_wpcom_vip() {
	return defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV;
}

// IMPORTANT: We need to load all VIP must have plugins before we load our PMC plugins
PMC_Must_Have_Plugins::load_vip_plugins(); // @codeCoverageIgnore

/*
 * Load classes
 */
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-timemachine.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-singleton.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-save-post-delayed-tasks.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-dom.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-feature.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-shortcode.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-cache.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-scripts.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-variable.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-user-roles.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-plugin-loader.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-global.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-ajax.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-cdn.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-cheezcap.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-cheezcap-ajax-button.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-inappropriate-for-syndication.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-inject-content.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-wpcom-legacy-redirector-extras.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-image-widget.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-smart-app-banners.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/php/pmc-image.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/deprecated.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-image-metadata.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-cookie.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-api.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-image-credit.php';
require PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-cheezcap-secure-input.php';

// *** IMPORTANT ***
// Must have plugins must be load and activated before any other plugins that has any dependencies on these plugins.
PMC_Must_Have_Plugins::load_pmc_plugins();  // @codeCoverageIgnore

// DO NOT activate any plugins before this line

\PMC\Global_Functions\HTTPS::get_instance();

// If this is here after 1 October 2020, yell at Jorbin.
\PMC\Global_Functions\Jquery_Migrate::get_instance();

// Load Evergreen_Content class.
\PMC\Global_Functions\Evergreen_Content::get_instance();

// Load Admin Media Validation class.
\PMC\Global_Functions\PMC_Admin_Media_Validation::get_instance();

// Load PMC_Cookie class.
\PMC\Global_Functions\Classes\PMC_Cookie::get_instance();

// Load post-sync cleanup handlers for VIP Go.
\PMC\Global_Functions\VIP_Go_Sync_Cleanup::get_instance();

// Load Jetpack customizations.
\PMC\Global_Functions\Jetpack_Customizations::get_instance();

// Load WP REST API endpoint manager.
\PMC\Global_Functions\WP_REST_API\Manager::get_instance();

// Load style-inlining utilities to ensure output hooks are added.
\PMC\Global_Functions\Styles::get_instance();

\PMC_Global::get_instance();

// Load NDN everywhere except Go
if( (defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV) && ( ! defined( 'PMC_IS_VIP_GO_SITE' ) || ! PMC_IS_VIP_GO_SITE ) ) {
	pmc_load_plugin( 'pmc-ndn', 'pmc-plugins' );
}

if( is_admin() ) {
	require( PMC_GLOBAL_FUNCTIONS_PATH . '/classes/class-pmc-admin-utilities.php' );

	add_action( 'admin_enqueue_scripts', function() {
		wp_enqueue_style( 'pmc-wpadmin-overrides', plugins_url( 'css/pmc-wpadmin-overrides.css', __FILE__ ) );
	} );
}

/**
 * Hide all notices and warnings in the head, add a CSS class so we can explicitly hide the unwanted warning, and restore all notices in the footer.
 * Prevent the warning/error notice from flashing, even once. Degrades gracefully.
 *
 * @since 2016-03-28 Corey Gilmore
 * @todo Use PMC_Groups to opt-in PMC staff, continue to avoid annoying editorial staff.
 *
 */
if( is_admin() ) {

	add_action( 'admin_head', function() {
		?>
		<style>.notice.notice-warning.vip-php-warning { display: none; } .notice.notice-warning { display: none; }</style>
		<?php
	}, 100 );

	add_action( 'admin_print_footer_scripts', function() {
		?>
		<script>if( window.jQuery ) { jQuery(".notice.notice-warning:contains('PHP errors or warnings')").addClass('vip-php-warning'); }</script>
		<style>.notice.notice-warning { display: block; }</style>
		<?php
	}, 100 );
}

// Enable the PMC XMLRPC Server for all sites
if( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST === true ) {
	pmc_load_plugin( 'pmc-xmlrpc-server', 'pmc-plugins' );
}

// 4.2 terms spliting fix / migration - need to load after pmc-wp-cli.php
if( ! defined ('PMC_IS_VIP_GO_SITE' ) || PMC_IS_VIP_GO_SITE !== true ) {
	pmc_load_plugin('pmc-term-split','pmc-plugins');
}

// supress attachment's description from rendering
add_filter( 'the_content', function( $content ) {
	if ( is_attachment() ) {
		return '';
	}
	return $content;
}, 1 );

// any functions call that need to execute in init action should append to the list.below
add_action( 'init', function() {

	// prevent shortcode [source] from applying to fix var[source] issue in template
	// https://wordpressvip.zendesk.com/requests/21761
	remove_shortcode( 'source' );

	// Restore Open Graph's original behavior of showing images instead of video snapshots
	// https://wordpressvip.zendesk.com/requests/23328
	remove_filter( 'jetpack_open_graph_tags', 'enhanced_og_video' );
	remove_filter( 'jetpack_open_graph_tags', 'enhanced_og_image' );
	remove_filter( 'jetpack_open_graph_tags', 'enhanced_og_gallery' );

	/**
	 * Disable global terms on WordPress.com.  Affecting term split if not disabled
	 */
	if ( function_exists( 'wpcom_vip_disable_global_terms' ) ) {
		if ( 'no' == PMC_Cheezcap::get_instance()->get_option( 'pmc_global_terms' ) ) {
			wpcom_vip_disable_global_terms();
		}
	}

	// Enable VIP Performance Tweaks
	// View the function for a list of the gains it accomplishes
	if ( function_exists( 'wpcom_vip_enable_performance_tweaks' ) ){
		wpcom_vip_enable_performance_tweaks();
	}
} );

/**
 * Load PMC and/or WPCOM plugins
 *
 * @param array $plugins A multi-dimensional array of plugins to load.
 *                       See example below.
 *
 * Using this function allows parent and child themes to add or remove
 * plugins which might exist in the parent theme. It also provides a
 * unified way to load plugins between VIP and VIPGO.
 *
 * Example:
 *
 * pmc_load_plugins ( array(
 *     'pmc-plugins' => array(
 *         'pmc-disable-comments',
 *         'pmc-ads'
 *         ...
 *     ),
 *     'plugins' => array (
 *         'cache-nav-menu',
 *         'co-authors-plus',
 *         'fieldmanager' => '1.1', // Optionally specify a version
 *         ...
 *     )
 * ) );
 *
 */
function load_pmc_plugins( $plugins ) {

	if ( ! is_array( $plugins ) ) {
		$plugins = array();
	}

	$plugins = apply_filters( 'load_pmc_plugins', $plugins );
	$plugins_include = apply_filters( 'load_pmc_plugins_include', array() );
	$plugins_exclude = apply_filters( 'load_pmc_plugins_exclude', array() );

	if ( ! empty( $plugins_include ) || ! empty( $plugins_exclude ) ) {
		$plugins = load_pmc_plugins_filter( $plugins, $plugins_include, $plugins_exclude );
		unset ( $plugins_include );
		unset ( $plugins_exclude );
	}

	if ( empty( $plugins ) ) {
		return;
	}

	foreach ( $plugins as $folder => $list ) {
		if ( empty( $list ) || empty( $folder ) ) {
			continue;
		}
		foreach ( $list as $key => $value ) {

			$plugin = $plugin_folder = $version = false;

			if ( is_numeric( $key ) ) {
				$plugin = $value;
			} else {
				$plugin = $key;
				$version = $value;
			}

			if ( empty( $plugin ) ) {
				continue;
			}

			/**
			 * @change 2016-05-04 Corey Gilmore
			 *
			 * Don't pass 'plugins' for $folder, use the functions
			 * default param value. Added here for cross-compatibility
			 * with VIPGO which expects to find VIP shared plugins in WP_PLUGINS_DIR
			 */
			$plugin_folder = $folder;
			if( $folder === 'plugins' ) {
				$plugin_folder = false;
			}

			pmc_load_plugin( $plugin, $plugin_folder, $version );
		}
	}

	do_action( 'load_pmc_plugins_loaded' );
} // load_pmc_plugins

// helper function to apply $include & $exclude to $plugins list
function load_pmc_plugins_filter( $plugins, $include = array(), $exclude = array() ) {

	$return_plugins = array();

	// $plugins need to be an array
	if ( ! is_array( $plugins ) ) {
		$plugins = array();
	}

	$keys = array_merge( array_keys( $include ), array_keys( $exclude ) );
	$keys = array_merge( $keys, array_keys( $plugins ) );
	$keys = array_unique( $keys );

	foreach ( $keys as $key ) {

		$list = array();

		if ( isset( $plugins[ $key ] ) && is_array( $plugins[ $key ] ) ) {

			$list = $plugins[ $key ];

			if ( isset( $exclude[$key] ) && is_array( $exclude[$key] ) ) {
				$list = array_diff( $list, $exclude[$key] );
			}

		}

		if ( isset( $include[ $key ] ) && is_array( $include[ $key ] ) ) {
			$list = array_merge( $list, $include[ $key ] );
		}

		$list = array_filter( array_unique( $list ) );

		if ( !empty( $list ) ) {
			$return_plugins[ $key ] = $list;
		}

	}

	return $return_plugins;
} // function load_pmc_plugins_filter

function pmc_add_body_class( $class ) {
	PMC_Global::get_instance()->add_body_class( $class );
}

function pmc_remove_body_class( $class ) {
	PMC_Global::get_instance()->remove_body_class( $class );
}

function pmc_set_body_class ( $add_class, $remove_class = array() ) {
	pmc_add_body_class( $add_class );
	pmc_remove_body_class( $remove_class );
}


/**
 * Sanitizes an HTML attribute name to ensure it only contains valid characters.
 * Virtually identical to sanitize_html_class() in WordPress core, duplicated to provide flexibility.
 *
 * Strips the string down to A-Z,a-z,0-9,_,-. If this results in an empty
 * string then it will return the alternative value supplied.
 *
 * @author Corey Gilmore (originally in BGR)
 * @since 2013-12-07 Corey Gilmore Initial Version
 *
 * @see sanitize_html_class
 *
 * @param string $attr_name The attribute name to be sanitized
 * @param string $fallback Optional. The value to return if the sanitization results in an empty string.
 *      Defaults to an empty string.
 * @return string The sanitized value
 */
function pmc_sanitize_html_attribute_name( $attr_name, $fallback = '' ) {
	//Strip out any % encoded octets
	$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $attr_name );
	//Limit to A-Z,a-z,0-9,_,-
	$sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', $sanitized );
	if ( '' == $sanitized ) {
		$sanitized = $fallback;
	}
	return $sanitized;
}


/**
 * Search the content provided for a shortcode name and returns all matches
 *
 * @param $content Post content to search
 * @param $shortcode The name of the shortcode to find.
 *
 * @return array array of matches (empty if no matches)
 *
 * @since 2014-12-05 Corey Gilmore
 * @see http://codex.wordpress.org/Function_Reference/get_shortcode_regex
 *
 */
function pmc_find_all_shortcode( $content, $shortcode ) {
	$pattern = get_shortcode_regex();
	$shortcodes = array();

	if( preg_match_all( '/'. $pattern .'/s', $content, $matches, PREG_SET_ORDER ) ) {
		foreach( $matches as $m	 ) {
			// [[foo]] syntax for escaping a tag; this isn't a match
			if ( $m[1] == '[' && $m[6] == ']' ) {
				$x++;
				continue;
			}

			if( $m[2] == $shortcode ) {
				$shortcodes[] = array(
					'shortcode'  => $m[0],
					'tag'        => $m[2],
					'content'    => $m[5],
					'attr'       => shortcode_parse_atts( $m[3] ),
				);
			}
		}
	}

	return $shortcodes;
}

/**
 * Search the content provided for a shortcode and return the match
 * @see http://codex.wordpress.org/Function_Reference/get_shortcode_regex
 * @since 2012-10-31 Corey Gilmore (Moved from BGR)
 * @param $content
 * @param $shortcode
 * @param null $matches
 * @param null $index
 * @return bool
 *
 * @version 2014-12-05 Corey Gilmore deprecated in favor of `pmc_find_all_shortcode()`
 * @deprecated 2014-12-05 This does not work correctly with multiple shortcode matches, and has been replaced with `pmc_find_all_shortcode()`
 *
 * @see pmc_find_all_shortcode()
 *
 */
function pmc_find_shortcode( $content, $shortcode, $matches=null, $index=null) {
	$pattern = get_shortcode_regex();
	if( preg_match_all( '/'. $pattern .'/s', $content, $matches )
			&& array_key_exists( 2, $matches )
			&& in_array( $shortcode, $matches[2] )
	) {
		$index = array_search( $shortcode, $matches[2] );
		if( $index !== FALSE ) {
			return $matches[0][$index];
		}
	}

	$matches = null;
	$index = null;
	return FALSE;
}

/**
 * Return human readable time within x hour, otherwise return the date time format
 * @param  int  $time       The time
 * @param  integer $human_hour The hour diff to return as human time
 * @param  string  $format     The time format
 * @return string              The time
 */
function pmc_human_time( $time, $human_hour = 8, $format = 'M j, Y g:i a' ) {
	$to = current_time( 'timestamp' );
	$time_diff = $to - $time;

	//If time difference is less then $human_hour hours then show human time diff
	if ( $time_diff > 0 && $time_diff < $human_hour * HOUR_IN_SECONDS ) {
		return sprintf( __( '%s ago' ), human_time_diff( $time, $to ) );
	}

	// otherwise return time as $format
	return date( $format, $time );
}

/**
 * Legacy function referencing namespaced function.
 *
 * @todo remove this after confident all references to this function has been updated to namespaced function.
 *
 * @return array
 */
function pmc_get_intermediate_image_sizes() {
   return \PMC\Image\get_intermediate_image_sizes();
}

/**
 * @see plugins_url
 *
 * Retrieves a URL within the plugins or mu-plugins directory.
 *
 * Defaults to the plugins directory URL if no arguments are supplied.
 *
 * @since 2.6.0
 *
 * @param  string $path   Optional. Extra path appended to the end of the URL, including
 *                        the relative directory if $plugin is supplied. Default empty.
 * @param  string $plugin Optional. A full path to a file inside a plugin or mu-plugin.
 *                        The URL will be relative to its directory. Default empty.
 *                        Typically this is done by passing `__FILE__` as the argument.
 * @return string Plugins URL link with optional paths appended.
 */
function pmc_maybe_minify_url( $path = '', $plugin = '' ) {

	if ( PMC::is_production() && ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ) {
		if ( ! preg_match( '/.min./', $path ) ) {
			$path = str_replace( [ '.js', '.css' ], [ '.min.js', '.min.css' ], $path );
			$path = apply_filters( 'pmc_maybe_minify_url', $path );
		}
	}

	return plugins_url( $path, $plugin );
}

/**
 * This helper function is use to load the international theme override
 * For example on vipgo: vip-config/vip-config.php
 *
 * if ( ! defined( 'PMC_THEME_INTERNATIONAL_OVERRIDE_PATH' ) ) {
 * 	define( 'PMC_THEME_INTERNATIONAL_OVERRIDE_PATH', WP_CONTENT_DIR . '/themes/vip/pmc-robbreport-uk' );
 * }
 *
 */
function pmc_load_theme_international_override() {
	if ( ! defined('PMC_THEME_INTERNATIONAL_OVERRIDE_PATH') ) {
		return;
	}
	$theme_international_override_file = PMC_THEME_INTERNATIONAL_OVERRIDE_PATH . '/functions.php';
	if ( file_exists( $theme_international_override_file ) && validate_file( $theme_international_override_file ) === 0 ) {
		require_once $theme_international_override_file;
	}
}


/**
 * Helper function to schedule wp cron job to avoid typo and mistake
 * that can potentially cause issue with all options exploded
 * @param int    $timestamp  Unix timestamp (UTC) for when to next run the event.
 * @param string $recurrence How often the event should subsequently recur. See wp_get_schedules() for accepted values.
 * @param string $hook       Action hook to execute when the event is run.
 * @param array  $args       Optional. Array containing each separate argument to pass to the hook's callback function.
 * @return bool
 */
function pmc_schedule_event( int $timestamp, string $recurrence, string $hook, array $args = [] ) : bool {
	if ( empty( $timestamp ) || empty( $recurrence ) || empty( $hook ) ) {
		return false;
	}
	if ( ! wp_next_scheduled( $hook, $args ) ) {
		return (bool) wp_schedule_event(
			$timestamp,
			$recurrence,
			// These follow two arguments must match wp_next_scheduled check
			$hook,
			$args
		);
	}
	return false;
}

// This function loads global plugins that's required on all sites. Please dont move its position.

PMC_Plugin_Loader::load_global_plugins();

//EOF
