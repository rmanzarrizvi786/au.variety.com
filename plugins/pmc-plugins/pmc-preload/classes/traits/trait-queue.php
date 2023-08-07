<?php
/**
 * Queue manager.
 *
 * @package pmc-preload
 */

namespace PMC\Preload\Traits;

use PMC\Preload\Manager;

/**
 * Trait Queue.
 */
trait Queue {
	/**
	 * Items to process.
	 *
	 * @var array
	 */
	protected $_queue = [];

	/**
	 * Completed items.
	 *
	 * @var array
	 */
	protected $_done = [];

	/**
	 * Register hooks when singleton is instantiated.
	 *
	 * @codeCoverageIgnore Nothing to test on its own.
	 */
	protected function _init(): void {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {}

	/**
	 * Enqueue something to be preloaded.
	 *
	 * @codeCoverageIgnore Cannot test due to reliance on \PMC\Global_Functions\Traits\Singleton.
	 *
	 * @param string $item Item to queue.
	 */
	public static function add( string $item ): void {
		static::get_instance()->_add( $item );
	}

	/**
	 * Enqueue something to be preloaded.
	 *
	 * @param string $item Item to queue.
	 */
	abstract protected function _add( string $item ): void;

	/**
	 * Process queue.
	 */
	public function process(): void {
		foreach ( $this->_queue as $key => $item ) {
			$this->_process_item( $item );

			unset( $this->_queue[ $key ] );
		}
	}

	/**
	 * Process queued item.
	 *
	 * @param mixed $item Item to process.
	 */
	//abstract protected function _process_item( $item ): void;
}
