<?php
/**
 * bootstrap initialize the plugin
 */

namespace PMC\Uls;
use PMC\Global_Functions\Traits\Singleton;
use \CheezCapTextOption;
use \CheezCapDropdownOption;
use \PMC_Cheezcap;

class Session {
	use Singleton;

	private $_purge_queues = [];

	protected function __construct() {

	}

	/**
	 * Return true if ULS bypass is enabled
	 * @return boolean
	 */
	public function is_bypass() {

		$data = Plugin::get_instance()->get_authentication_data();

		if ( ! empty( $data->bypass ) ) {
			return ( 1 === intval( $data->bypass ) );
		}

		return false;

	}

	/**
	 * Return true if session allow access to a product code
	 * @param  string $code The product code to check
	 * @return boolean
	 */
	public function can_access( $code ) {

		if ( empty( $code ) ) {
			return false;
		}

		$entitlement = $this->entitlement();

		// valid entitled entries should be in an array
		if ( is_array( $entitlement ) ) {
			return in_array( $code, $entitlement, true );
		}

		if ( is_string( $entitlement ) ) {
			return $code === $entitlement;
		}

		return false;

	}

	/**
	 * Return true if user can access ANY of the code listed
	 * @param  array $codes The array of codes to check
	 * @return boolean
	 */
	public function can_access_any( $codes ) {

		if ( empty( $codes ) ) {
			return false;
		}

		$codes = (array) $codes;

		foreach ( $codes as $code ) {
			if ( $this->can_access( $code ) ) {
				// only need to meet one condition
				return true;
			}
		}

		return false;

	}

	/**
	 * Return true if user can access ALL of the code listed
	 * @param  array $codes The array of codes to check
	 * @return boolean
	 */
	public function can_access_all( $codes ) {

		if ( empty( $codes ) ) {
			return false;
		}

		$codes = (array) $codes;

		foreach ( $codes as $code ) {
			if ( ! $this->can_access( $code ) ) {
				// If any of the codes failed, entire check failed
				return false;
			}
		}

		return true;

	}

	/**
	 * Return a list of product code from entitlement records
	 * @return array[string]
	 */
	public function entitlement() {

		$data = Plugin::get_instance()->get_authentication_data();

		if ( ! empty( $data->entitlement ) ) {
			return $data->entitlement;
		}

		return false;

	}

}

