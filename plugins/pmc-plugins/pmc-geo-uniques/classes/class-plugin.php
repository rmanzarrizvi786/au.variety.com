<?php
namespace PMC\Geo_Uniques;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class to Manage all Geo related code
 *
 */

class Plugin {

	use Singleton;

	/**
	 * List of default countries.
	 *
	 * @var array
	 */
	public $default_countries = [
		'us',
	];

	/**
	 * List of European countries.
	 *
	 * @var array
	 */
	public $eu_countries = [
		'at',
		'be',
		'bg',
		'hr',
		'cy',
		'cz',
		'dk',
		'ee',
		'fi',
		'fr',
		'de',
		'gr',
		'hu',
		'ie',
		'it',
		'lv',
		'lt',
		'lu',
		'mt',
		'nl',
		'pl',
		'pt',
		'ro',
		'sk',
		'si',
		'es',
		'se',
		'gb',
		'is',
		'li',
		'no',
		'ch',
	];

	// Hold the class instance.
	private static $instance = null;

	/**
	 * constructor
	 *
	 * @codeCoverageIgnore
	 *
	 */
	protected function __construct() {
		add_action( 'init', [ $this, 'pmc_geo_add_default_locations' ], 3 );
		add_action( 'init', [ $this, 'pmc_geo_add_eu_locations' ], 3 );
	}

	/**
	 * Function to retrieve country region code. Ex:eu for European
	 *
	 * @return string
	 */
	public function pmc_geo_get_region_code() : string {
		$country_code = pmc_geo_get_user_location();
		if ( ! empty( $country_code ) && in_array( $country_code, (array) $this->eu_countries, true ) ) {
			return 'eu';
		}

		return 'other';

	}

	/**
	 * Adding list of supported EU countries.
	 */
	public function pmc_geo_add_eu_locations() {
		foreach ( $this->eu_countries as $country ) {
			pmc_geo_add_location( strtolower( $country ) );
		}
	}

	/**
	 * Adding list of supported default countries.
	 */
	public function pmc_geo_add_default_locations() {
		//Adding this here for now. This needs to be added from theme
		pmc_geo_set_default_location( 'us' );

		foreach ( $this->default_countries as $country ) {
			pmc_geo_add_location( strtolower( $country ) );
		}
	}

}
