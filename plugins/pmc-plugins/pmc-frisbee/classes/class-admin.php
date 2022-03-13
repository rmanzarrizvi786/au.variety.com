<?php

/**
 * Frisbee js
 *
 * @author Dan Berko <dberko@pmc.com>
 */

namespace PMC\Frisbee;

use PMC\Global_Functions\Traits\Singleton;

class Admin {
	use Singleton;

	/**
	 * Initialising Frisbee admin options
	 */
	protected function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue minified or unminified Frisbee script
	 */
	public function enqueue_scripts() {
		// This should prevent accidental double enqueuing if this plugin is loaded explicitly by a theme
		if ( ! pmc_js_libraries_get_registered_scripts( 'pmc-frisbee-js', PMC_FRISBEE_VER ) ) {
			$script_src = ( \PMC::is_production() ) ? PMC_FRISBEE_SRC_MIN : PMC_FRISBEE_SRC;
			wp_enqueue_script(
				'pmc-frisbee-js',
				$script_src,
				array(),
				PMC_FRISBEE_VER,
				false
			);
		};
	}
}
