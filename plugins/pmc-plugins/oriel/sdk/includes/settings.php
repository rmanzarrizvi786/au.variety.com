<?php

namespace Oriel;

/**
 * Oriel Plugin settings
 */
global $oriel_settings;

$oriel_settings = new \ArrayObject(
	array(
		'debug' => false,
		'api_key' => '', // Your Website API Key
		'api_url' => 'https://gw.oriel.io/api', // Main API URL
		'head_key_cache_ttl' => 60, // Head key cache TTL (seconds)
		'head_script_cache_ttl' => 60, // Head script cache TTL (seconds)
		'head_script_piggybacking' => false,
		'head_script_merging_url_match' => '', // Merged head script URL affinity
		'head_script_merging_cache_ttl' => 3600, // Merged head script cache TTL (seconds)
		'insert_head_script' => true,
		'inline_serving' => false, // Serves Oriel inline script
		'nuke_message' => '<div id="_ophdr" style="position:absolute;top:0; left:0; padding:20px; height: 100%; width: 100%; text-align: center; margin:auto;"><div style="margin-top: 300px; font-weight:bold;">Please make sure you have JavaScript enabled and ad-blocking extensions disabled in your browser in order to use this website.</div></div>',
		'dom_parser' => false,
		'in_page_messages' => array(),
		'enable_remote_settings' => true,
		'settings_cache_ttl' => 300,
		'sdk_header' => 'ORIEL-PHP',
		'sdk_version' => '8.7.6',
		// If this parameter will be added to the url, then the oriel plugin will be disabled.
		'disable_oriel_param' => 'disable_oriel',
		// The key to be used for image obfuscation.
		'obfuscation_key' => null,
		// Flag to decide if the image sources should be obfuscated.
		'obfuscate_image_sources' => false,
		// Filter to decide which images to obfuscate. By default all images should be obfuscated.
		'image_filter' => null,
		// Flag to decide if the picture sources should be obfuscated.
		'obfuscate_picture_sources' => false,
		'attributes_to_obfuscate' => [ 'src', 'srcset' ],
		// Filter to decide which picture tags to obfuscate. By default all picture tags should be obfuscated.
		'picture_filter' => null,
		// The default placeholder for the image and picture tags
		'image_source_placeholder' => 'data:image/gif;base64,R0lGODlhAQABAID/AP///wAAACwAAAAAAQABAAACAkQBADs=',
		// If this is set, then a noscript tag should be added to the page's body.
		// The noscript tag should contain a img with the same src as this property.
		'noscript_beacon_url' => null,
	), \ArrayObject::ARRAY_AS_PROPS
);


function process_settings_constraints( $settings ) {
	$settings->insert_head_script  = false;
	$settings->head_script_merging = false;
	$settings->enforce_js          = false;
}

process_settings_constraints( $oriel_settings );


