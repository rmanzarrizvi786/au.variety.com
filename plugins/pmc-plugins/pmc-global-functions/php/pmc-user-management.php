<?php
/**
 * WordPress-specific user management settings.
 *
 * @package WordPress
 * @subpackage pmc-plugins
 *
 * @since 2014-12-08 Corey Gilmore
 *
 */


/**
 * Enable bulk user management.
 *
 * @see http://lobby.vip.wordpress.com/2012/07/09/bulk-user-management-plugin/
 * @since 2014-12-08 Corey Gilmore
 *
 */
if( function_exists( 'wpcom_vip_bulk_user_management_whitelist' ) ) {
	wpcom_vip_bulk_user_management_whitelist(array(
		// Engineering
		'adaezeesiobu',       // Adaeze
		'aholisky',           // Adam
		'aguptapmc',          // Amit Gupta
		'pmcamit',            // Amit Sannad
		'coreygilmore',       // Corey
		'mintindeed',         // Gabriel
		'hauong',             // Hau
		'imgriff',            // Javier

		// Product
		'cyeohpmc',           // Christina
		'westcoastderek',     // Derek
		'duanevarietyla',     // Duane Rochester
		'nicolacatton',       // Nici Catton
		'tylerpmc',           // Tyler

		// Help Desk
		'mattwilliamson2013', // Matt Williamson

		// People with annoyingly long usernames
		'supercalifornialiciousexpialidocious', // Craig vanGorden
	));

	// Improve the styling of the bulk user management a bit
	add_action('admin_enqueue_scripts', function() {
		// @TODO: Add a check for a specific screen here
		wp_enqueue_style( 'pmc-bulk-user-management', plugins_url( 'pmc-bulk-user-management.css', __FILE__ ) );
	});

}


// EOF