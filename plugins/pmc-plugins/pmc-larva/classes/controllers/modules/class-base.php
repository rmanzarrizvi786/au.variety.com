<?php
/**
 * Module Base Class.
 *
 * Abstract class to provide default structure for module controllers,
 * especially a specified data object structure, render method, and
 * assertion of contextual options' adherence to the object structure.
 *
 * @package pmc-larva
 *
 * @since   2021-03-22
 */

namespace PMC\Larva\Controllers\Modules;

use ErrorException;
use PMC\Global_Functions\Traits\Singleton;
use PMC\Larva\Pattern;

abstract class Base {

	// TODO: they're meant to be separate instances, why not new it instead?
	use Singleton;

	/**
	 * Data to be provided by child classes.
	 */
	public $pattern_shortpath = '';

	/**
	 * Properties only for the abstract class.
	 *
	 * TODO: they're not private, so the docblock is incorrect. Determine intent.
	 */
	protected $_variant      = '';
	protected $_data         = [];
	protected $_pattern_json = [];

	/**
	 * Initialize the module with contextual options,
	 * and deconstruct the provided options into specific values
	 * for use in this abstract class.
	 *
	 * @param array $args Contextual options provided when the
	 *                    controller is called in a template.
	 *
	 * @return self Return an instance of the class to support chained
	 *              function calls.
	 */
	final public function init( array $args = [] ): self {

		$defaults       = $this->get_default_options();
		$merged_options = $this->_parse_args( $args, $defaults );

		$this->_variant = $merged_options['variant'];
		$this->_data    = $merged_options['data'];

		$this->_pattern_json = Pattern::get_instance()->get_json_data( $this->pattern_shortpath . '.' . $this->_variant );

		return $this;
	}

	/**
	 * The default options structure for the module. This structure
	 * serves as a kind of "contract" for any data that is sent to
	 * the Larva module specified for the class. This "contract" is
	 * enforced before passing rendering the template with data.
	 *
	 * @return array Object to ultimately be passed to the pattern.
	 */
	abstract public function get_default_options(): array;

	/**
	 * Manually map provided data to the pattern JSON object.
	 *
	 * @param array $pattern The Larva pattern JSON object to plugin data into.
	 * @param array $data    Actual data to override placeholder data.
	 *
	 * @return array Object to ultimately be passed to render_template.
	 */
	abstract public function populate_pattern_data( array $pattern, array $data ): array;

	/**
	 * Render the pattern with data.
	 *
	 * @param boolean $echo Default false, or do not echo the string.
	 *
	 * @return string Markup string with contextual data.
	 */
	final public function render( bool $echo = false ): string {
		return Pattern::get_instance()->render_pattern_template(
			$this->pattern_shortpath,
			$this->larva_data(),
			$echo
		);
	}

	/**
	 * Populate and validate Larva data.
	 *
	 * @return array
	 */
	final public function larva_data(): array {
		$object = $this->populate_pattern_data(
			$this->_pattern_json,
			$this->_data
		);

		$this->_assert_pattern_object_structure(
			$object,
			$this->_pattern_json
		);

		return $object;
	}

	/**
	 * A wrapper for wp_parse_args to additionally parse the `data`
	 * key's array value.
	 *
	 * This method can be expanded in the future if more
	 * recursive arg parsing is required.
	 *
	 * @param array $args     The context-specific arguments
	 * @param array $defaults The defaults to retain
	 *
	 * @return array An array containing the new options and defaults where
	 *               new ones have not been provided.
	 */
	final private function _parse_args( array $args, array $defaults = [] ): array {

		$merged_options = wp_parse_args( $args, $defaults );

		$merged_options['data'] = wp_parse_args( $args['data'], $defaults['data'] );

		return $merged_options;
	}

	/**
	 * Helper method to assert object structures match, specifically
	 * intended to handle cases where the structure of the pattern object
	 * after adding data doesn't match the initial structure.
	 *
	 * @param array $populated The pattern object populated with data
	 * @param array $original  The original pattern object retrieved from JSON
	 *
	 * @return array          Array containing keys for result of assertion and
	 *                        optionally a missing key.
	 * @throws ErrorException Invalid data structure.
	 */
	final private function _assert_pattern_object_structure( array $populated, array $original ): array {

		$errors = [];

		foreach ( $original as $key => $value ) {

			if ( ! isset( $populated[ $key ] ) ) {
				$errors[] = 'Missing key: ' . $key;
			}

			if ( is_array( $value ) ) {

				$is_optional_pattern = false === $populated[ $key ];
				$is_empty_list       = empty( $populated[ $key ] );

				if ( $is_optional_pattern || $is_empty_list ) {
					continue;
				}

				// If not false or empty, recursively check the array for adherence
				return $this->_assert_pattern_object_structure( $populated[ $key ], $original[ $key ] );
			}

		}

		if ( ! empty( $errors ) ) {
			throw new ErrorException( 'Pattern object with data does not match JSON object. ' . implode( ', ', $errors ), 1 );
		}

		return $errors;
	}

}
