<?php

/**
 * Generic singleton class to hold variables to be use in templates for passing parameters and or shared data between templates
 *
 * @since 2013-09-05 Hau Vong
 *
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Variable {

	use Singleton;

	/*
	 * unset all object's properties
	 *
	 * @param string @var_name (optional) variable name to unset, otherwise unset all
	 * @return void
	 */
	public function reset( $var_name = false ) {

		if ( !empty( $var_name ) ) {
			unset ( $this->$var_name );
			return;
		}

		$vars = get_object_vars( $this );

		if ( empty ( $vars ) ) {
			return;
		}

		foreach ( $vars as $key => $value ) {
			unset ( $this->$key );
		}
	}

	/*
	 * @param string @var_name variable name
	 * @return void
	 */
	public function set ( $var_name, $var_value ) {
		$this->$var_name = $var_value;
	}

	/*
	 * Retrieve variable data
	 *
	 * @param string $var_name variable name to retrieve
	 * @param mixed $default default value if variable isn't set
	 * @return mixed
	 */
	public function get ( $var_name, $default = false ) {
		if ( isset( $this->$var_name ) ) {
			return $this->$var_name;
		}
		return $default;
	}

	/*
	 * Retrieve variable data and unset it from object
	 *
	 * This method would be useful for passing variable parameters to template
	 *
	 * @see function get
	 * @return mixed
	 */
	public function consume( $var_name, $default = false ) {
		$value = $this->get( $var_name, $default );
		$this->reset( $var_name );
		return $value;
	}

}
