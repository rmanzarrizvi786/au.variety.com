<?php
/**
 * Larva Json.
 *
 * Class for providing methods for working with Larva pattern JSON.
 *
 * @package pmc-plugins
 * @since   2020-07-22
 */

namespace PMC\Larva;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Template.
 *
 * @since 2020.07.22
 */
class Pattern {

	use Singleton;

	/**
	 * Locate the file path for a pattern file by first looking in the brand directory,
	 * and if the file is not found, then retrieve the file from Larva core directory.
	 *
	 * @param $placeholders           A placeholder pattern argument for sprintf.
	 * @param $config_key_identifier  The portion of the config key pairing that identifies
	 *                                the nature of the data, and comes after brand_ or core_
	 * @param $pattern_name           The name of the pattern to be retrieved
	 *                                        ex: modules/breadcrumbs
	 *                                            modules/author.featured-article
	 */
	private static function _locate_file_path( string $placeholders, string $config_key_identifier, string $pattern_name ) : string {

		$file_path = sprintf(
			$placeholders,
			\PMC\Larva\Config::get_instance()->get( 'brand_' . $config_key_identifier ),
			$pattern_name
		);

		// Fallback to the core version of the file, if brand version does not exist.
		if ( false === \PMC::is_file_path_valid( $file_path ) ) {
			$file_path = sprintf(
				$placeholders,
				\PMC\Larva\Config::get_instance()->get( 'core_' . $config_key_identifier ),
				$pattern_name
			);

			if ( false === \PMC::is_file_path_valid( $file_path ) ) {
				throw new \ErrorException( 'Could not find file for ' . $pattern_name . ' in ' . $file_path );
			}
		}

		return $file_path;
	}

	/**
	 * Remove some mocking from Larva data that needs to be replaced or text run through translation function.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private static function _remove_mocking_from_data( array $data ): array {
		// Larva keys or partial keys that should return an empty value. example: c_link_text.
		$pattern = implode(
			'|',
			[
				'_alt_attr',
				'_image_alt_attr',
				'_markup',
				'_srcset_attr',
				'_text',
				'_url',
			]
		);

		$remove_func = function( $key, $value ) use ( $pattern ) {
			if ( is_array( $value ) ) {
				return [ $key, self::_remove_mocking_from_data( $value ) ];
			}

			// regex101: https://regex101.com/r/ZV4exW/1
			if ( preg_match( '/(' . $pattern . ')$/', $key ) ) {
				$value = '';
			}

			return [ $key, $value ];
		};

		return self::_array_map_assoc( $remove_func, $data );
	}

	/**
	 * Helper to map an associate array.
	 *
	 * @param callable $function
	 * @param array    $array
	 *
	 * @return array
	 */
	private static function _array_map_assoc( callable $function, array $array ): array {
		return array_column( array_map( $function, array_keys( $array ), $array ), 1, 0 );
	}

	/**
	 * A wrapper around PMC::render_template that checks the brand for a template
	 * and if it doesn't exist, renders the template from Larva core.
	 *
	 * @param      $pattern_name
	 *                          ex:
	 *                          modules/breadcrumbs
	 *                          modules/main-menu
	 *                          objects/o-nav
	 *                          components/c-button
	 * @param      $data
	 * @param      $echo
	 *
	 * @return string|object
	 * @throws \Exception
	 */
	public static function render_pattern_template( string $pattern_name, array $data = [], bool $echo = false ) : string {

		$file_path = self::_locate_file_path( '%s/%s.php', 'templates_directory', $pattern_name );

		return \PMC::render_template( $file_path, $data, $echo );

	}

	/**
	 * Get data structure from JSON File. Check for JSON in the brand directory,
	 * and if it doesn't exist, get it from the Larva core.
	 *
	 * @param      $pattern_name
	 *                          ex:
	 *                          modules/breadcrumbs.featured-article
	 *                          modules/main-menu.prototype
	 *                          objects/o-nav.prototype
	 *                          components/c-button.prototype
	 * @param bool $remove_mocking
	 *                            when `true` some mocking is automatically
	 *                            removed from data to prevent it from
	 *                            displaying on production
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function get_json_data( $pattern_name, bool $remove_mocking = false ) {

		$pattern_name = apply_filters( 'pmc_larva_json_pattern_name', $pattern_name );
		$file_path    = self::_locate_file_path( '%s/build/json/%s.json', 'directory', $pattern_name );

		$json = \PMC::render_template(
			$file_path,
			[],
			false
		);

		$data = (array) json_decode( $json, true );

		if ( ! empty( $remove_mocking ) ) {
			$data = self::_remove_mocking_from_data( $data );
		}

		return $data;

	}

}
