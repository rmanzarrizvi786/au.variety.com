<?php

/**
 * Default Oriel settings
 */

global $wpsettings;

$wpsettings = array(
	'activation_url'     => 'https://oriel.io/console/dashboard/integration/wordpress/activate',
	'sdk_header'         => 'WP',
	'enable_inbound_api' => true,
);

/**
 * Get options from DB
 */

$options = get_option( 'oriel_options' );
if ( ! is_array( $options ) ) {
	$options = array();
}

/**
 * Compute the final Oriel settings array
 */
$wpsettings += $options;

// If the key is not set, then remove it in order to use the fallback.
if ( isset( $wpsettings['disable_oriel_param'] ) && '' === $wpsettings['disable_oriel_param'] ) {
	unset( $wpsettings['disable_oriel_param'] );
}

if ( function_exists( 'is_wpe' ) && is_wpe() ) {
	$wpsettings['is_wpe'] = true;
}

if ( function_exists( 'cloudflare_init' ) ) {
	$wpsettings['uses_cloudflare'] = true;
}


if ( isset( $wpsettings['is_wpe'] ) && $wpsettings['is_wpe'] ) {
	$wpsettings['sdk_header']              .= '/E';
	$wpsettings['head_script_piggybacking'] = false;
}

/**
 * Include $cache variable
 */
require_once plugin_dir_path( __FILE__ ) . 'class-cache.php';

