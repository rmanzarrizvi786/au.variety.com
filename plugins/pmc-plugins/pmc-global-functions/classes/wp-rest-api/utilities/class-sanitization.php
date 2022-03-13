<?php
/**
 * Utilities for sanitizing REST inputs.
 *
 * @package pmc-global-functions
 */

namespace PMC\Global_Functions\WP_REST_API\Utilities;

/**
 * Class Sanitization.
 */
class Sanitization {
	/**
	 * Sanitize an input as a boolean value.
	 *
	 * @param mixed $param Raw value.
	 * @return bool
	 */
	public static function boolean( $param ): bool {
		if ( 'false' === $param ) {
			return false;
		}

		return (bool) $param;
	}
}
