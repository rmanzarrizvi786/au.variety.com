<?php
/**
 * Larva Json.
 *
 * Class for providing methods for working with Larva pattern JSON.
 *
 * @package pmc-plugins
 * @since   2020-05-08
 */

namespace PMC\Larva;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Json.
 *
 * @since 2019.08.26
 */
class Json {

	use Singleton;

	/**
	 * Get data structure from JSON File. Child themes can use the filter
	 * pmc_larva_json_file_path to change the location of the JSON.
	 *
	 * @param      $pattern_name
	 *                          ex:
	 *                          modules/breadcrumbs.featured-article
	 *                          modules/main-menu.prototype
	 *                          objects/o-nav.prototype
	 *                          components/c-button.prototype
	 * @param bool $from_plugin
	 *
	 * @deprecated
	 *
	 * @return array|mixed|object
	 * @throws \Exception
	 */
	public function get_json_data( $pattern_name ) {

		_deprecated_function(
			__METHOD__,
			'pmc-larva',
			'\PMC\Larva\Pattern::get_json_data'
		);

		return \PMC\Larva\Pattern::get_instance()->get_json_data( $pattern_name );
	}

}
