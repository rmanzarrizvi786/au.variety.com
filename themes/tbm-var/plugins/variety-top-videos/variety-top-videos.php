<?php
// Load plugin code
require_once( __DIR__ . '/class-variety-top-videos.php' );
require_once( __DIR__ . '/class-variety-top-videos-data.php' );
require_once( __DIR__ . '/class-variety-top-videos-settings.php' );

// Grab or create a new instance of our Top Videos class
Variety_Top_Videos::get_instance();

/**
 * The Data class was used to fetch additional video data from YouTube via their API
 * We're no longer using this functionality. Leaving for posterity as it may be resurrected at a later date.
 */
//Variety_Top_Videos_Data::get_instance();

// Grab or create a new instance the Top Video settings page class
Variety_Top_Videos_Settings::get_instance();

/**
 * Add video keyword to ads.
 */
function pmc_variety_top_videos_custom_ad_keywords( $keywords ) {
	global $post;

	if ( is_post_type_archive( 'variety_top_video' ) ||
		 ( is_single( $post ) && 'variety_top_video' === $post->post_type )
	   ) {
			$keywords[] = 'video';
	}

	return array_unique( $keywords );
} // function pmc_variety_top_videos_custom_ad_keywords

add_filter( 'mmc_add_custom_keywords_ad', 'pmc_variety_top_videos_custom_ad_keywords' );

//EOF
