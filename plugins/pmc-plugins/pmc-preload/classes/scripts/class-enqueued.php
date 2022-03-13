<?php
/**
 * Preload enqueued scripts.
 *
 * @package pmc-preload
 */

namespace PMC\Preload\Scripts;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Preload\Traits\Queue;
use PMC\Preload\Traits\WP_Dependencies;
use WP_Scripts;

/**
 * Class Enqueued.
 */
class Enqueued {
	use Queue;
	use WP_Dependencies;
	use Singleton;

	/**
	 * Dependency type.
	 *
	 * @return string
	 */
	protected function _type(): string {
		return 'script';
	}

	/**
	 * Retrieve object containing all registered scripts and other necessary data.
	 *
	 * @return WP_Scripts
	 */
	protected function _dependency_manager(): WP_Scripts {
		global $wp_scripts;
		return $wp_scripts;
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		// Running very, very late lest we preload a script we don't use because it's been concatenated.
		add_filter( 'js_do_concat', [ $this, 'should_concat_handle' ], 9999, 2 );
	}

	/**
	 * Enqueue something to be preloaded.
	 *
	 * @param string $item Script handle to queue.
	 */
	protected function _add( string $item ): void {
		if ( empty( $item ) ) {
			return;
		}

		$this->_queue[] = $item;
	}

	/**
	 * Process queued item.
	 *
	 * @param string $item Script handle.
	 */
	protected function _process_item( string $item ): void {
		if ( ! wp_script_is( $item, 'enqueued' ) ) {
			return;
		}

		$this->_render_preload_tag_for_registered_dependency( $item );
	}

	/**
	 * Prevent a preloaded script from being concatenated, otherwise it will be
	 * loaded twice. http/2 diminishes concatenation's benefit, which is made up
	 * for by the gain preloading introduces.
	 *
	 * @param bool   $concat Whether or not to concatenate this script.
	 * @param string $handle Script handle.
	 * @return bool
	 */
	public function should_concat_handle( bool $concat, string $handle ): bool {
		if ( in_array( $handle, (array) $this->_done, true ) ) {
			$concat = false;
		}

		return $concat;
	}
}
