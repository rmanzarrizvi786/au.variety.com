<?php

namespace PMC\Store_Products;

use PMC\Global_Functions\Traits\Singleton;

class Setup {

	use Singleton;

	/**
	 * Setup constructor.
	 * 
	 * @codeCoverageIgnore Class constructor does not need code coverage, because it just calls other methods which should have their own code coverage.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup Hooks.
	 */
	protected function _setup_hooks() {

		add_filter( 'the_content', [ $this, 'tracking_id_replacements' ], 11 );

	}

	/**
	 * Replace old tracking IDs with new ones if any order set.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function tracking_id_replacements( $content ) : string {

		$config  = apply_filters( 'pmc_store_products_tracking_id_replacements', [] );
		$content = (string) $content;

		if ( ! empty( $config ) && is_array( $config ) ) {
			foreach ( $config as $key => $value ) {
				$key     = sprintf( 'tag=%s', sanitize_key( $key ) );
				$value   = sprintf( 'tag=%s', sanitize_key( $value ) );
				$content = str_replace( $key, $value, $content );
			}
		}

		return $content;

	}

}

// EOF
