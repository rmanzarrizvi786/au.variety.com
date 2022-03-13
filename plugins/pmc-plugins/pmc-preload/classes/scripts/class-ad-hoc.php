<?php
/**
 * Preload ad-hoc scripts (those not enqueued), such as those from pmc-tags.
 *
 * @package pmc-preload
 */

namespace PMC\Preload\Scripts;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Preload\Traits\Queue;
use PMC\Preload\Traits\WP_Dependencies;
use _WP_Dependency;
use WP_Scripts;

/**
 * Class Ad_Hoc.
 */
class Ad_Hoc {
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
	 * Enqueue something to be preloaded.
	 *
	 * @param string $item Script URL to queue.
	 */
	protected function _add( string $item ): void {
		if ( empty( $item ) ) {
			return;
		}

		$dep = new _WP_Dependency(
			'pmc-preload-ad-hoc-' . md5( $item ),
			$item,
			[],
			null,
			null
		);

		$this->_queue[] = $dep;
	}

	/**
	 * Process queued item.
	 *
	 * @param _WP_Dependency $item Script to preload.
	 */
	protected function _process_item( _WP_Dependency $item ): void {
		$this->_render_preload_tag( $item, false );
	}
}
