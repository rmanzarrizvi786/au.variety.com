<?php


class OrielUtil {

	private static $_site_path;

	public static function init() {
		$url = explode( '/', site_url() );
		if ( count( $url ) >= 3 ) {
			unset( $url[0], $url[1], $url[2] );
		}
		self::$_site_path = implode( '/', $url );
	}

	public static function site_path( $suffix = '' ) {
		return self::$_site_path . $suffix;
	}

	public static function get_headers_from_request( $header ) {
		$headers = explode( "\r\n", strtoupper( $header ) );
		$ret     = array();
		foreach ( $headers as $line ) {
			$line                    = explode( ':', $line, 2 );
			$ret[ trim( $line[0] ) ] = trim( $line[1] );
		}
		return $ret;
	}

	/**
	 * Checks if URL contains /`slug`/ or ends with /`slug`
	 * Default `slug` is "amp"
	 */
	public static function is_amp_endpoint() {
		$is_amp = false;

		if ( function_exists( 'amp_get_slug' ) ) {
			$amp_slug     = '/' . amp_get_slug() . '/';
			$amp_slug_end = '/' . amp_get_slug();
		} else {
			$amp_slug     = '/amp/';
			$amp_slug_end = '/amp';
		}

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$url = strtok( wp_unslash( $_SERVER['REQUEST_URI'] ), '?' ); // WPCS: sanitization okay
		} else {
			$url = '';
		}
		$amp_slug_length = strlen( $amp_slug_end );

		if ( strpos( $url, $amp_slug ) !== false || substr( $url, -$amp_slug_length ) === $amp_slug_end ) {
			$is_amp = true;
		}

		return $is_amp;
	}

}

OrielUtil::init();


