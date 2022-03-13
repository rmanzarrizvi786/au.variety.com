<?php

namespace PMC\Cxense;

/**
 * Class Cxense_Module_Manager
 * @package PMC\Cxense
 */
class Cxense_Modules implements \JsonSerializable {
	/**
	 * The array of modules to send to Cxense.
	 *
	 * @var array
	 */
	protected $modules = [];

	public function __construct( array $module_data ) {
		foreach ( $module_data as $module ) {
			$this->add_module( $module );
		}
	}

	/**
	 * Adds a module after validating that it doesn't already exist.
	 *
	 * @param array $module_data
	 */
	public function add_module( array $module_data ): void {
		$module = $this->create_module( $module_data );

		if ( ! empty( $module ) && ! $this->module_exists( $module ) ) {
			$this->modules[] = $module;
		}
	}

	/**
	 * Creates a module after validating that needed parameters exist.
	 *
	 * @param array $module_data
	 *
	 * @return array
	 */
	public function create_module( array $module_data ): array {
		if ( $this->is_valid_data( $module_data ) ) {
			return [
				'widgetId'        => $module_data['module_id'],
				'targetElementId' => $module_data['div_id'],
			];
		}

		return [];
	}

	/**
	 * Checks to see if the module already exists in the manager.
	 *
	 * @param array $module
	 *
	 * @return bool
	 */
	public function module_exists( array $module ): bool {
		return ( stripos( wp_json_encode( $this->get_modules() ), wp_json_encode( $module ) ) !== false );
	}

	/**
	 * Checks to make sure needed array keys exist, then checks for array values
	 *
	 * @param array $module_data
	 *
	 * @return bool
	 */
	public function is_valid_data( array $module_data ): bool {
		if ( ! $this->array_keys_exist( $module_data ) ) {
			return false;
		}

		return $this->validate_array_values( $module_data );
	}

	/**
	 * Array key check
	 *
	 * @param array $module_data
	 *
	 * @return bool
	 */
	public function array_keys_exist( array $module_data ): bool {
		return ( array_key_exists( 'div_id', $module_data ) && array_key_exists( 'module_id', $module_data ) );
	}

	/**
	 * Array value validation
	 *
	 * @param array $module_data
	 *
	 * @return bool
	 */
	public function validate_array_values( array $module_data ): bool {
		return $this->array_values_exist( $module_data ) && ! $this->is_paywall_module( $module_data['div_id'] );
	}

	/**
	 * Check if values are empty
	 *
	 * @param  array  $module_data
	 *
	 * @return bool
	 */
	public function array_values_exist( array $module_data ): bool {
		return ! empty( $module_data['div_id'] ) && ! empty( $module_data['module_id'] );
	}

	/**
	 * Check if paywall module was manually added. It should be added separately in theme config.
	 * @see https://confluence.pmcdev.io/display/ENG/Dynamic+Paywall
	 *
	 * @param  string  $module_div_id
	 *
	 * @return bool
	 */
	public function is_paywall_module( string $module_div_id ): bool {
		return 'cx-paywall' === $module_div_id;
	}

	/**
	 * Filters out invalid modules so we don't try to send them to Cxense.
	 *
	 * @return array
	 */
	public function get_modules(): array {
		return $this->modules;
	}

	/**
	 * Serialized module data to send to Cxense.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->get_modules();
	}
}
