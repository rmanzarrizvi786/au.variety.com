<?php
/**
 * We're using PMC Options plugin to store the data as cache.
 * Because the data hardly get changes and only update when needed.
 * Stored the data locally would speed up the newsletter UI management without trigger ET API Service
 */

namespace PMC\Exacttarget;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Options;

class Cache {
	use Singleton;

	// Defined the related constant values to be used by this plugin
	const OPTION_GROUP = 'pmc_exacttarget_cache';

	private $_option = null;

	protected function __construct() {
		$this->_option = PMC_Options::get_instance( self::OPTION_GROUP );
	}

	/**
	 * Common helper function to reuse code with optional sorting of array returned
	 * @param $name
	 * @param bool $sort
	 * @return array|mixed
	 */
	public function et_get( string $name, bool $sort = true ) {
		$result = $this->_option->get_option( $name );
		if ( empty( $result ) ) {
			$method = 'get_' . $name;
			$result = \Exact_Target::$method();
			if ( $sort && is_array( $result ) ) {
				asort( $result );
			}
			$this->_option->update_option( $name, $result );
		}
		return $result;
	}

	/**
	 * Return the sorted ET Templates
	 * @return array|mixed
	 */
	public function get_templates() {
		return $this->et_get( 'templates' );
	}

	/**
	 * Return the sorted ET Templates
	 * @return array|mixed
	 */
	public function get_templates_from_content_builder() {
		return $this->et_get( 'templates_from_content_builder' );
	}

	/**
	 * Return the sorted ET Data Extenions
	 * @return array|mixed
	 */
	public function get_data_extensions() {
		return $this->et_get( 'data_extensions' );
	}

	/**
	 * Return the sorted ET Send Classifications
	 * @return array|mixed
	 */
	public function get_sendclassifications() {
		return $this->et_get( 'sendclassifications' );
	}

	/**
	 * Clear out all stored data
	 * @return $this
	 */
	public function invalidate() : self {
		foreach ( (array) $this->_option->get_options() as $key => $value ) {
			$this->_option->delete_option( $key );
		}

		return $this;
	}

	/**
	 * Clear out stored data and prime the cache by retrieving the data from ET API Service
	 * @return $this
	 */
	public function refresh() : self {
		$this->invalidate();
		$this->get_templates();
		$this->get_data_extensions();
		$this->get_sendclassifications();
		$this->get_templates_from_content_builder();
		return $this;
	}

}
