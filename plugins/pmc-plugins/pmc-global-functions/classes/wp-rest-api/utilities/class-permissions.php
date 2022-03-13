<?php
/**
 * Endpoint utilities for checking permissions.
 *
 * @package pmc-global-functions
 */

namespace PMC\Global_Functions\WP_REST_API\Utilities;

use Closure;

/**
 * Class Permissions.
 */
class Permissions {
	/**
	 * Restrict an endpoint using a WordPress user capability.
	 *
	 * @param string $capability Capability to check.
	 * @return Closure
	 */
	public static function current_user_can( string $capability ): Closure {
		// Bug in WPCS when `use` is used: https://github.com/WordPress/WordPress-Coding-Standards/issues/1071.
		// phpcs:ignore WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterStructureOpen, WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceBeforeOpenParenthesis
		return static function() use( $capability ): bool {
			return current_user_can( $capability );
		};
	}
}
