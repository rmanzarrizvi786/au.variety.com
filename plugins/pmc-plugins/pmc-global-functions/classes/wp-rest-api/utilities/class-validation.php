<?php
/**
 * Endpoint utilities for validating parameters.
 *
 * @package pmc-global-functions
 */

namespace PMC\Global_Functions\WP_REST_API\Utilities;

/**
 * Class Validation.
 */
class Validation {
	/**
	 * Check if parameter is numeric.
	 *
	 * @param mixed $param Parameter to check.
	 * @return bool
	 */
	public static function is_numeric( $param ): bool {
		return is_numeric( $param );
	}
}
