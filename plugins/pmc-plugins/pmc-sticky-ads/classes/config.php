<?php
/**
 * Config class for PMC Sticky Ads plugin.
 * It stores different config values for each service
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2016-12-12
 */

namespace PMC\Sticky_Ads;


use \PMC\Global_Functions\Traits\Singleton;
use \ErrorException;

class Config {

	use Singleton;

	protected $_services = array(

		'mobile' => array(),
		'desktop' => array(),

	);

	/**
	 * Method to set config for a service
	 *
	 * @param string $service Name of service for which config is to be stored
	 * @param array $config An array of key => value pairs that are to be stored for the specified service
	 * @return void
	 */
	public function set_for_service( $service = 'mobile', array $config = array() ) {

		if ( ! is_string( $service ) ) {
			throw new ErrorException( sprintf( '%s::%s() - Service parameter must be a string', get_called_class(), __FUNCTION__ ) );
		}

		$service = strtolower( trim( $service ) );

		if ( empty( $service ) || empty( $config ) ) {
			throw new ErrorException( sprintf( '%s::%s() - Service & Config parameters cannot be empty', get_called_class(), __FUNCTION__ ) );
		}

		if ( ! isset( $this->_services[ $service ] ) || ! is_array( $this->_services[ $service ] ) ) {

			$this->_services[ $service ] = array();

		}

		foreach ( $config as $key => $value ) {

			// If value is empty then don't store it
			if ( ! is_bool( $value ) && ! is_numeric( $value ) && empty( $value ) ) {
				continue;
			}

			$this->_services[ $service ][ $key ] = $value;

		}

	}

	/**
	 * Method to set config for a service for a specific key
	 *
	 * @param string $service Name of service for which config is to be stored
	 * @param string $key Key name for which value is to be stored
	 * @param mixed $value Value which is to be stored
	 * @return void
	 */
	public function set_single_for_service( $service = 'mobile', $key = '', $value = '' ) {

		if ( ! is_string( $service ) || ! is_string( $key ) ) {
			throw new ErrorException( sprintf( '%s::%s() - Service & Key parameters must be strings', get_called_class(), __FUNCTION__ ) );
		}

		$service = strtolower( trim( $service ) );
		$key = strtolower( trim( $key ) );

		if ( empty( $service ) || empty( $key ) || ( ! is_bool( $value ) && ! is_numeric( $value ) && empty( $value ) ) ) {
			throw new ErrorException( sprintf( '%s::%s() - No parameter passed can be empty', get_called_class(), __FUNCTION__ ) );
		}

		$this->set_for_service( $service, array(
			$key => $value,
		) );

	}

	/**
	 * Method to get all the config data of a service
	 *
	 * @param string $service Name of service whose config is to be fetched
	 * @return array An array of key => value pairs that are stored for the specified service
	 */
	public function get_for_service( $service = 'mobile' ) {

		if ( ! is_string( $service ) ) {
			throw new ErrorException( sprintf( '%s::%s() - Service parameter must be a string', get_called_class(), __FUNCTION__ ) );
		}

		$service = strtolower( trim( $service ) );

		if ( empty( $service ) ) {
			throw new ErrorException( sprintf( '%s::%s() - Service parameter cannot be empty', get_called_class(), __FUNCTION__ ) );
		}

		if ( isset( $this->_services[ $service ] ) ) {
			return $this->_services[ $service ];
		}

		return array();

	}

	/**
	 * Method to get config value of a specific key of a service
	 *
	 * @param string $service Name of service whose config is to be fetched
	 * @param string $key Key name whose value is to be fetched
	 * @param mixed $default Default value which is to be returned if key or service does not exist
	 * @return mixed
	 */
	public function get_single_for_service( $service = 'mobile', $key = '', $default = false ) {

		if ( ! is_string( $service ) || ! is_string( $key ) ) {
			throw new ErrorException( sprintf( '%s::%s() - Service & Key parameters must be strings', get_called_class(), __FUNCTION__ ) );
		}

		$service = strtolower( trim( $service ) );
		$key = strtolower( trim( $key ) );

		if ( empty( $service ) || empty( $key ) ) {
			throw new ErrorException( sprintf( '%s::%s() - Service & Key parameters cannot be empty', get_called_class(), __FUNCTION__ ) );
		}

		if ( isset( $this->_services[ $service ][ $key ] ) ) {
			return $this->_services[ $service ][ $key ];
		}

		return $default;

	}

}	//end of class


//EOF
