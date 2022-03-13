<?php
/**
 * Apply SEO tweaks.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class SEO.
 */
class SEO {
	use Singleton;

	/**
	 * SEO constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function _setup_hooks(): void {
		add_filter( 'pmc_meta_robots_noindex', [ $this, 'prevent_indexing' ] );
	}

	/**
	 * Exclude Digital Daily pages from indexing.
	 *
	 * @param bool $noindex If page should include noindex meta.
	 * @return bool
	 */
	public function prevent_indexing( bool $noindex ): bool {
		if ( is_dd() ) {
			return true;
		}

		return $noindex;
	}
}
