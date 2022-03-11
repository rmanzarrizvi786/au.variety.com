<?php
/*
variety hollywood executives Profile Module
Description: variety hollywood executives Profile module for the theme to create/store variety hollywoo dexecutives profiles as custom post_type & display using its own template
Author: Amit Gupta

@since 2011-11-09
@version 2012-06-22 Amit Gupta
@version 2013-07-17 Adaeze Esiobu
*/

define( 'VARIETY_HOLLYWOOD_EXECUTIVES_PROFILE_VERSION', '1.5.0' );
define( 'VARIETY_HOLLYWOOD_EXECUTIVES_ROOT', __DIR__ );

add_action( 'after_setup_theme', 'variety_hollywood_executives_profile_loader' );

function variety_hollywood_executives_profile_loader() {

	require_once VARIETY_HOLLYWOOD_EXECUTIVES_ROOT . '/class-variety-hollywood-executives-profile.php';		//load variety_hollywood_executives  class
	require_once VARIETY_HOLLYWOOD_EXECUTIVES_ROOT . '/class-variety-hollywood-executives-admin.php';		//load variety_hollywood_executives  admin class

	require_once VARIETY_HOLLYWOOD_EXECUTIVES_ROOT . '/class-variety-hollywood-executives-api.php';

	require_once VARIETY_HOLLYWOOD_EXECUTIVES_ROOT . '/class-variety-hollywood-executives-rest-api-logger.php';
	require_once VARIETY_HOLLYWOOD_EXECUTIVES_ROOT . '/class-variety-hollywood-executives-profiles-api.php';
	require_once VARIETY_HOLLYWOOD_EXECUTIVES_ROOT . '/class-variety-hollywood-executives-rest-api.php';

	require_once VARIETY_HOLLYWOOD_EXECUTIVES_ROOT . '/class-variety-hollywood-executives-taxonomy-inspector.php';

	if ( ! isset( $GLOBALS['variety_hollywood_executives_admin'] ) ) {
		$GLOBALS['variety_hollywood_executives_admin'] = new Variety_Hollywood_Executives_Profile_Admin();	//variety_hollywood_executives  admin class object
	}
	if ( ! isset( $GLOBALS['variety_hollywood_executives'] ) ) {
		$GLOBALS['variety_hollywood_executives'] = new Variety_Hollywood_Executives_Profile();	// variety_hollywood_executives class object
	}

}


/**
 * Add Variety Hollywood Executive Profile post type to sitemaps so that Variety sitemaps can also
 * include Hollywood Executive Profiles
 *
 * @since 2013-07-23 Amit Gupta
 */
add_filter( 'pmc_sitemaps_post_type_whitelist', 'variety_hollywood_executives_add_to_sitemaps' );
function variety_hollywood_executives_add_to_sitemaps( $post_types ) {
	if( ! is_array( $post_types ) ) {
		return $post_types;
	}

	if( ! in_array( 'hollywood_exec', $post_types, true ) ) {
		$post_types[] = 'hollywood_exec';
	}

	return $post_types;
}

//EOF
