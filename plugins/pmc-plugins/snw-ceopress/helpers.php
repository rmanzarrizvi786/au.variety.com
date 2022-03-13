<?php
/**
 * HELPER FUNCTIONS
 */

namespace SNW\CEO_Press;

/**
 * snw_get_remote - cleaner curl usage throughout plugin
 *
 * @param string $url    URL to get/put data to/from CEOPress
 * @param string $method GET/PUT method for REMOTE REQUEST
 * @param string $uuid   UUID (optional)
 * @param array $body   Optional. Request arguments. Default empty array.
 *
 * @return mixed|\WP_Error
 */
function snw_get_remote( $url, $method, $uuid = '', $body = [] ) {
	//get the saved api settings
	$ceopress_config = API_Settings::get_instance()->get_config();

	//check that we have saved settings
	if ( ! $ceopress_config ) {
		$output = new \WP_Error( 'CEO API Missing', esc_html__( 'Please enter API Settings in Theme Settings -> CEO API Settings' ) );

		return $output;
	}

	$wp_args = [];

	$base_url = $ceopress_config['url'];
	$api_key  = $ceopress_config['key'];
	$full_url = sprintf(
		'%s/%s/%s/?per_page=150',
		untrailingslashit( $base_url ),
		untrailingslashit( $url ),
		untrailingslashit( $uuid )
	);

	$wp_args['headers'] = [
		'Content-Type'  => 'application/json',
		'Authorization' => 'Basic ' . base64_encode( $api_key ) . ':',
	];

	$wp_args['method'] = $method;
	$wp_args['body']   = $body;

	$output = wp_remote_request( $full_url, $wp_args );

	if ( ! is_wp_error( $output ) ) {
		$output = json_decode( $output['body'] );
	}

	return $output;
}

/**
 * snw_redirect - simple redirect
 *
 * @param string $url URL to redirect
 */
function snw_redirect( $url ) {

	if ( empty( $url ) || ! is_string( $url ) ) {
		return;
	}

	wp_safe_redirect( $url );
	exit;

}

/**
 * snw_compare_dates - compare dates
 *
 * @param object $a
 * @param object $b
 *
 * @return int An integer less than, equal to, or greater than zero when $a is respectively less than, equal to, or greater than $b
 */
function snw_compare_dates( $a, $b ) {
	$utc = new \DateTimeZone( 'UTC' );

	$t1 = \DateTime::createFromFormat( 'Y-m-d H:i:s', $a->export->modified_at, $utc );
	$t2 = \DateTime::createFromFormat( 'Y-m-d H:i:s', $b->export->modified_at, $utc );

	return $t2 <=> $t1;
}

/**
 * Validate UUID format.
 *
 * @param string $str UUID to validate
 *
 * @return bool true if $str is valid uuid else false.
 */
function snw_is_uuid( $str ) {

	$uuidv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';

	return ( 1 === preg_match( $uuidv4, $str ) );
}
