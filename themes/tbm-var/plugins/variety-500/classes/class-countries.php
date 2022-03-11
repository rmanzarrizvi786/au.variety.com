<?php
/**
 * Countries
 *
 * Responsible for country specific data.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

namespace Variety\Plugins\Variety_500;

/**
 * Class Countries
 *
 * Returns countries.
 *
 * @since 1.0
 */
class Countries {
	/**
	 * Get Country Slug
	 *
	 * Returns a country code for a country.
	 *
	 * @since 1.0
	 * @param string $country_name The name of the country to fetch the slug for.
	 * @return string
	 */
	public static function get_country_slug( $country_name ) {
		$country_name = strtolower( trim( $country_name ) );
		$country_name = str_replace( ' ', '_', $country_name );
		return $country_name;
	}
}

// EOF.
