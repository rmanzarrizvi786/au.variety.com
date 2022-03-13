<?php
/**
 * Class to set/get config
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-11-25
 */

namespace PMC\Region_Redirect_Overlay;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;
use \PMC_Cheezcap;
use \ErrorException;

class Config {

	use Singleton;

	/**
	 * @var int Duration (in days) in which overlay is not to be shown to same user again
	 */
	protected $_dnd_duration = 30;

	/**
	 * @var array Array containing countries for which the overlay is to be displayed.
	 *
	 * The data in this array should be COUNTRY_CODE => COUNTRY_NAME. The plugin uses
	 * Geo location coming in via Fastly, so the two letter Country Code should match
	 * the one that would come from there.
	 *
	 * Example of values stored in this array
	 * [
	 *     'fr' => __( 'France', 'domain' ),
	 *     'kr' => __( 'South Korea', 'domain' ),
	 *     'dk' => __( 'Denmark', 'domain' ),
	 * ]
	 */
	protected $_countries = [];

	/**
	 * @var array Array containing HTML to show in overlay for specific countries.
	 *
	 * Example of values stored in this array
	 * [
	 *     'fr' => 'You are visiting international version of our site. Click here to go to our French website.',
	 *     'kr' => 'Looking for our Korean website? Click here to go to our Korean website.',
	 *     'dk' => 'We have a Danish website too. Click here to go to our Danish website.',
	 * ]
	 */
	protected $_overlay_html = [];

	/**
	 * Method to get the DND duration
	 *
	 * @return int
	 */
	public function get_dnd_duration() : int {
		return intval( $this->_dnd_duration );
	}

	/**
	 * Method to set the DND duration
	 *
	 * @param int $days
	 *
	 * @return \PMC\Region_Redirect_Overlay\Config
	 */
	public function set_dnd_duration( int $days ) : self {

		$this->_dnd_duration = ( 0 < $days ) ? $days : $this->_dnd_duration;

		return $this;

	}

	/**
	 * Method to get the set countries
	 *
	 * @return array
	 */
	public function get_countries() : array {
		return (array) $this->_countries;
	}

	/**
	 * Method to get countries selected in admin settings
	 *
	 * @return array
	 */
	public function get_selected_countries() : array {

		$selected_countries = PMC_Cheezcap::get_instance()->get_option( Admin::OPTION_COUNTRIES );
		$selected_countries = ( empty( $selected_countries ) || ! is_array( $selected_countries ) ) ? [] : $selected_countries;

		return $selected_countries;

	}

	/**
	 * Method to set the countries
	 *
	 * @param array $countries
	 *
	 * @return \PMC\Region_Redirect_Overlay\Config
	 *
	 * @throws \ErrorException
	 */
	public function set_countries( array $countries ) : self {

		if ( ! empty( $countries ) && ! PMC::is_associative_array( $countries ) ) {

			throw new ErrorException(
				sprintf(
					'%1$s::%2$s() expects an associative array',
					__CLASS__,
					__FUNCTION__
				)
			);

		}

		$this->_countries = $countries;

		return $this;

	}

	/**
	 * Method to get the HTML strings for set countries to display in the overlay
	 *
	 * @return array
	 */
	public function get_overlay_html() : array {
		return (array) $this->_overlay_html;
	}

	/**
	 * Method to get overlay HTML for countries enabled in admin settings
	 *
	 * @return array
	 */
	public function get_overlay_html_for_selected_countries() : array {

		$html_strings       = [];
		$selected_countries = $this->get_selected_countries();

		foreach ( $selected_countries as $country ) {

			if ( ! empty( $this->_countries[ $country ] ) && ! empty( $this->_overlay_html[ $country ] ) ) {
				$html_strings[ $country ] = $this->_overlay_html[ $country ];
			}

		}

		return $html_strings;

	}

	/**
	 * Method to set the overlay HTML strings corresponding to set countries
	 *
	 * @param array $html_strings
	 *
	 * @return \PMC\Region_Redirect_Overlay\Config
	 *
	 * @throws \ErrorException
	 */
	public function set_overlay_html( array $html_strings ) : self {

		if ( empty( $html_strings ) || ! PMC::is_associative_array( $html_strings ) ) {

			throw new ErrorException(
				sprintf(
					'%1$s::%2$s() expects a non-empty associative array',
					__CLASS__,
					__FUNCTION__
				)
			);

		}

		$this->_overlay_html = $html_strings;

		return $this;

	}

}  // end class

//EOF
