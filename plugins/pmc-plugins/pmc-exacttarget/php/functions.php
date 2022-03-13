<?php
use PMC\Exacttarget\Config;

// Helper function to decode json / serialize string for backward compatible data preparing for V2 integration
function pmc_et_maybe_decode( $content, $assoc = true ) {
	if ( is_string( $content ) ) {
		$decoded = json_decode( $content, $assoc );
		if ( JSON_ERROR_NONE === json_last_error() ) {
			return $decoded;
		}
		if ( is_serialized( $content ) ) {
			return @unserialize( $content, ['allowed_classes' => false] ); // phpcs:ignore
		}
	}
	return $content;
}

function sailthru_isset_notempty( $data ) {
	if ( isset( $data ) ) {
		if ( empty( $data ) ) {
			return false;
		} else {
			return true;
		}

	} else {
		return false;
	}
}

function sailthru_check_days( $day ) {
	$schedule_days = array(
		'Mon',
		'Tue',
		'Wed',
		'Thu',
		'Fri',
		'Sat',
		'Sun'
	);

	return in_array( $day, $schedule_days );
}

if( !function_exists('is_supported_sailthru_post_type') ){
    /*
 * returns true if the post type given is set as one of the post types that sailthru supports.
 * Added: 10-02-2012 Adaeze Esiobu
 */
	function is_supported_sailthru_post_type( $post_type ){
		$options = (array) Config::get_instance()->get( 'supported_post_types' );
		$supported_post_types = array_merge( array( 'post', 'page' ), $options );
		return in_array( $post_type, $supported_post_types, true );
	}
}

function sailthru_get_post_alert_tag( $post_id ) {

	$post_meta = get_post_meta( $post_id );

	$alert_keyword = wp_cache_get( 'saitlrhu_tag_keyword_' . $post_id, "sailthru" );

	if ( !empty( $alert_keyword ) ) {
		return $alert_keyword;
	}

	$alert_keyword = 'no';

	foreach ( $post_meta as $key => $value ) {
		if ( 0 === stripos( $key, "fastnewsletter_log" ) ) {
			$alert_keyword = "st-breaking-news";
			break;
		}
	}

	wp_cache_add( 'saitlrhu_tag_keyword_' . $post_id, $alert_keyword, "sailthru", 300 );

	return $alert_keyword;

}


//EOF
