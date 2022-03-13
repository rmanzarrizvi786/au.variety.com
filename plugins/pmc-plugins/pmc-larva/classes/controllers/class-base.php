<?php
/**
 * Base class for component and object controllers.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers;

/**
 * Class Base.
 */
abstract class Base {
	/**
	 * Controller arguments.
	 *
	 * @var array
	 */
	protected $_args;

	/**
	 * Base constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( array $args ) {
		$this->_args = $args;
	}

	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	abstract public function add_data( array &$data ): void;
}
