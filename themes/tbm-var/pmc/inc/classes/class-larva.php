<?php
/**
 * PMC Core Larva
 *
 * Class for dealing with Larva Patterns functionality.
 *
 * @package pmc-core
 * @since   2019-08-26
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use function UrbanAirship\Push\location;

/**
 * Class Larva.
 *
 * @since 2019.08.26
 */
class Larva {

	use Singleton;

	/**
	 * Get data structure from JSON File. Child themes can use the filter
	 * pmc_core_larva_json_file_path to change the location of the JSON.
	 *
	 * @param      $pattern_name
	 *                          ex:
	 *                          modules/breadcrumbs.featured-article
	 *                          modules/main-menu.prototype
	 *                          objects/o-nav.prototype
	 *                          components/c-button.prototype
	 * @param bool $from_parent_theme
	 *
	 * @return array|mixed|object
	 * @throws \Exception
	 */
	public function get_json( $pattern_name, $from_parent_theme = false ) {

		$path = ( true === $from_parent_theme ) ? PMC_CORE_PATH . '/larva' : CHILD_THEME_PATH . '/assets';

		$pattern_name = apply_filters( 'pmc_core_larva_json_pattern_name', $pattern_name );

		$file_path = sprintf( '%s/build/json/' . $pattern_name . '.json', untrailingslashit( $path ) );

		$file_path = apply_filters( 'pmc_core_larva_json_file_path', $file_path );

		$json = \PMC::render_template(
			$file_path,
			[],
			false
		);

		$data = json_decode( $json, true );

		return $data;
	}

}
