<?php

namespace Oriel;

/**
 * Class Util
 *
 * @package Oriel
 */
class Util {



	private static $_bots = array(
		'googlebot'   => array(
			'ip_ranges' => array(
				'64.233.0.0/16',
				'66.102.0.0/16',
				'66.249.0.0/16',
				'72.14.0.0/16',
				'74.125.0.0/16',
				'203.208.0.0/16',
				'209.85.0.0/16',
				'216.239.0.0/16',
			),
		),
		'msnbot'      => array(
			'ip_ranges' => array(
				'64.4.0.0/16',
				'65.52.0.0/16',
				'65.55.0.0/16',
				'131.253.0.0/16',
				'157.54.0.0/16',
				'157.55.0.0/16',
				'157.56.0.0/16',
				'199.30.0.0/16',
				'207.46.0.0/16',
				'207.68.0.0/16',
			),
		),
		'bingbot'     => array(
			'ip_ranges' => array(
				'64.4.0.0/16',
				'65.52.0.0/16',
				'65.55.0.0/16',
				'131.253.0.0/16',
				'157.54.0.0/16',
				'157.55.0.0/16',
				'157.56.0.0/16',
				'199.30.0.0/16',
				'207.46.0.0/16',
				'207.68.0.0/16',
			),
		),
		'bingpreview' => array(
			'ip_ranges' => array(
				'64.4.0.0/16',
				'65.52.0.0/16',
				'65.55.0.0/16',
				'131.253.0.0/16',
				'157.54.0.0/16',
				'157.55.0.0/16',
				'157.56.0.0/16',
				'199.30.0.0/16',
				'207.46.0.0/16',
				'207.68.0.0/16',
			),
		),
		'slurp'       => array(
			'ip_ranges' => array(
				'8.12.144.0/24',
				'66.196.0.0/16',
				'66.228.0.0/16',
				'67.195.0.0/16',
				'68.142.0.0/16',
				'72.30.0.0/16',
				'74.6.0.0/16',
				'98.136.0.0/16',
				'202.160.0.0/16',
				'209.191.0.0/16',
			),
		),
		'alexa'       => array(
			'ip_ranges' => array(
				'204.236.0.0/16',
				'75.101.0.0/16',
			),
		),
	);

	/**
	 * Returns the real IP address of the user
	 *
	 * @return string
	 */
	public static function get_ip() {
		//Just get the headers if we can or else use the SERVER global
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers = apache_request_headers();
		} else {
			$headers = $_SERVER;
		}

		//Get the forwarded IP if it exists
		if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$the_ip = $headers['X-Forwarded-For'];
		} elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$the_ip = $headers['HTTP_X_FORWARDED_FOR'];
		} else {
			if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$remote_addr = wp_unslash( $_SERVER['REMOTE_ADDR'] ); // WPCS: sanitization okay
			} else {
				$remote_addr = '';
			}
			$the_ip = filter_var( $remote_addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
		}
		return $the_ip;
	}

	public static function address_in_network( $ip, $range ) {
		if ( strpos( $range, '/' ) !== false ) {
			// $range is in IP/NETMASK format
			list($range, $netmask) = explode( '/', $range, 2 );
			if ( strpos( $netmask, '.' ) !== false ) {
				// $netmask is a 255.255.0.0 format
				$netmask     = str_replace( '*', '0', $netmask );
				$netmask_dec = ip2long( $netmask );
				return ( ( ip2long( $ip ) & $netmask_dec ) === ( ip2long( $range ) & $netmask_dec ) );
			} else {
				// $netmask is a CIDR size block
				// fix the range argument
				$x = explode( '.', $range );
				while ( count( $x ) < 4 ) {
					$x[] = '0';
				}
				list($a,$b,$c,$d) = $x;
				$range            = sprintf( '%u.%u.%u.%u', empty( $a ) ? '0' : $a, empty( $b ) ? '0' : $b, empty( $c ) ? '0' : $c, empty( $d ) ? '0' : $d );
				$range_dec        = ip2long( $range );
				$ip_dec           = ip2long( $ip );

				// Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
				// $netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

				// Strategy 2 - Use math to create it
				$wildcard_dec = pow( 2, ( 32 - $netmask ) ) - 1;
				$netmask_dec  = ~ $wildcard_dec;

				return ( ( $ip_dec & $netmask_dec ) === ( $range_dec & $netmask_dec ) );
			}
		} else {
			// range might be 255.255.*.* or 1.2.3.0-1.2.3.255
			if ( strpos( $range, '*' ) !== false ) { // a.b.*.* format
				// Just convert to A-B format by setting * to 0 for A and 255 for B
				$lower = str_replace( '*', '0', $range );
				$upper = str_replace( '*', '255', $range );
				$range = "$lower-$upper";
			}

			if ( strpos( $range, '-' ) !== false ) { // A-B format
				list($lower, $upper) = explode( '-', $range, 2 );
				$lower_dec           = (float) sprintf( '%u', ip2long( $lower ) );
				$upper_dec           = (float) sprintf( '%u', ip2long( $upper ) );
				$ip_dec              = (float) sprintf( '%u', ip2long( $ip ) );
				return ( ( $ip_dec >= $lower_dec ) && ( $ip_dec <= $upper_dec ) );
			}

			return false;
		}
	}

	/**
	 * Verifies whether the client who makes the request is a bot
	 *
	 * @return bool The verdict
	 */
	public static function is_bot() {

		$ip = self::get_ip();

		if ( $ua && $ip ) {
			foreach ( self::$_bots as $bot_name => $bot ) {
				foreach ( $bot['ip_ranges'] as $bot_net ) {
					if ( self::address_in_network( $ip, $bot_net ) ) {
						return true;
					}
				}
			}
		}
		return false;
	}

}

/**
 * Class HTMLHelper Acts as a wrapper for HTML based tasks
 *
 * @package Oriel
 */
class HTMLHelper {


	/**
	 *
	 * @param  string $tag_name - The tag to be extracted
	 * @param  string $html     The HTML source
	 * @return array|null
	 */
	public static function extract_tag( $tag_name, $html ) {
		preg_match_all( "/(<$tag_name.*?>)(.*?)(<\/$tag_name>)/si", $html, $matches );

		if ( count( $matches ) === 4 ) {
			return array(
				'full_match' => $matches[0][0],
				'start'      => $matches[1][0],
				'content'    => $matches[2][0],
				'end'        => $matches[3][0],
			);
		}
		return null;
	}

	/**
	 *
	 * @param  string $tag_name - The tag that will contain the content
	 * @param  string $content  - The content to be inserted
	 * @param  string $html     - The overall HTML content
	 * @return string - The processed HTML content
	 */
	public static function inject_content( $tag_name, $content, $html, $position = 'end' ) {
		$tag = self::extract_tag( $tag_name, $html );
		if ( ! $tag ) {
			return $html;
		}
		if ( 'end' === $position ) {
			$new_content = $tag['start'] . $tag['content'] . $content . $tag['end'];
		} else {
			$new_content = $tag['start'] . $content . $tag['content'] . $tag['end'];
		}

		return str_replace( $tag['full_match'], $new_content, $html );
	}
}
