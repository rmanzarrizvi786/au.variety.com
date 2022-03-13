<?php

namespace PMC\Cxense;

/**
 * Class to manage aggregation of custom parameters, including optional prefix for handling AMP pages.
 * @TODO: Validate parameters follow Cxense's rules outline here https://wiki.cxense.com/display/cust/Custom+parameters
 * @package pmc-cxense
 */
class Custom_Parameters implements \JsonSerializable {
	/**
	 * @var array
	 */
	protected $custom_parameters = [];

	/**
	 * @var string|null
	 */
	protected $prefix;

	/**
	 * Custom_Parameters constructor. Set prefix if needed, then adds all initial parameters.
	 * Prefix is added here instead of in the add_parameter function because it should be applied to all
	 * custom parameters if it's needed.
	 *
	 * @param array $custom_parameter_data
	 * @param string|null $prefix
	 */
	public function __construct( array $custom_parameter_data, ?string $prefix = '' ) {
		$this->prefix = $prefix;

		foreach ( $custom_parameter_data as $parameter_name => $parameter_value ) {
			$value = ( is_array( $parameter_value ) ) ? implode( ',', $parameter_value ) : $parameter_value;
			$this->add_parameter( $parameter_name, $value );
		}
	}

	/**
	 * Add an individual parameter, either from constructor or after object creation.
	 * Unlike modules, we do not need to check if the parameter name is empty because it is the key name from the array
	 * of custom parameters, so a key should always exist here.
	 * We want to consistently send the same set of parameters to Cxense, regardless of empty values,
	 * so no need to check if the value is empty here, either.
	 * In future, validation mentioned in the TO DO comment above may be implemented
	 *
	 * @param string $parameter_name
	 * @param string|null $parameter_value
	 */
	public function add_parameter( string $parameter_name, ?string $parameter_value ): void {
		$this->custom_parameters[ $this->prefix . $parameter_name ] = $parameter_value;
	}

	/**
	 * Gets all custom parameters as an array to be rendered within the JS to be sent to Cxense.
	 *
	 * @return array
	 */
	public function get_custom_parameters(): array {
		return $this->custom_parameters;
	}

	/**
	 * Allows this object to be properly serialized when calling json_encode.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->get_custom_parameters();
	}
}
