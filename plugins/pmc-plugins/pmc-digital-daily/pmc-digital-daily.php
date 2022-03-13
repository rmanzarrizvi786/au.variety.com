<?php
/**
 * Digital Daily features.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

/**
 * Slug of Digital Daily post type.
 */
const POST_TYPE = 'digital-daily';

/**
 * Slug of post type for special-edition articles.
 */
const POST_TYPE_SPECIAL_EDITION_ARTICLE = 'digital-daily-sea';

/**
 * Path to asset build directory.
 */
const BUILD_DIR_PATH = __DIR__ . '/assets/build/';

/**
 * URL for asset build directory.
 */
define(
	// Constant name is a string, sniff is confused.
	// phpcs:ignore WordPressVIPMinimum.Constants.ConstantString
	__NAMESPACE__ . '\BUILD_DIR_URL',
	plugins_url(
		'assets/build/',
		__FILE__
	)
);

// Load utility functions.
require_once __DIR__ . '/functions.php';

/**
 * Load plugin's singletons without needing to update for each new addition.
 */
( static function(): void {
	$prefixes = [
		__DIR__ . '/classes/class-'              => '',
		__DIR__ . '/classes/integrations/class-' => 'Integrations\\',
	];

	foreach ( $prefixes as $file_prefix => $namespace_suffix ) {
		foreach ( glob( $file_prefix . '*.php', 0 ) as $class ) {
			$class = str_replace(
				[
					$file_prefix,
					'.php',
					'-',
				],
				[
					'',
					'',
					'_',
				],
				$class
			);

			$class = __NAMESPACE__ . '\\' . $namespace_suffix . $class;

			if ( method_exists( $class, 'get_instance' ) ) {
				$class::get_instance();
			}
		}
	}
} )();
