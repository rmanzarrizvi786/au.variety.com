<?php

// Load plugin code
require_once( __DIR__ . '/dependencies.php' );
require_once( __DIR__ . '/class-pmc-top-videos.php' );
require_once( __DIR__ . '/class-pmc-top-videos-data.php' );
require_once( __DIR__ . '/class-pmc-top-videos-settings.php' );

// Grab or create a new instance of our Top Videos class
\PMC\Top_Videos_V2\PMC_Top_Videos::get_instance();

// Grab or create a new instance the Top Video settings page class
\PMC\Top_Videos_V2\PMC_Top_Videos_Settings::get_instance();

if ( class_exists( '\FM_Widget' ) ) {
	require_once( __DIR__ . '/class-video-featured.php' );
	// Create instance of the Video Featured class.
	\PMC\Top_Videos_V2\Video_Featured::get_instance();
}

/**
 * Add video keyword to ads.
 */
function pmc_top_videos_custom_ad_keywords( $keywords ) {
	global $post;

	if (
		is_post_type_archive( 'pmc_top_video' ) ||
		( is_single( $post ) && 'pmc_top_video' === $post->post_type )
	) {
			$keywords[] = 'video';
	}

	return array_unique( (array) $keywords );
}

add_filter( 'mmc_add_custom_keywords_ad', 'pmc_top_videos_custom_ad_keywords' );

//EOF
