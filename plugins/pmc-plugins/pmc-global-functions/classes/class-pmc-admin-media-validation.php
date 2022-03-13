<?php
/**
 * To enqueue Admin media validation resouces.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-08-16 READS-1409
 */

namespace PMC\Global_Functions;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Admin_Media_Validation {

	use Singleton;

	/**
	 * Register hooks
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		/**
		 * Actions.
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * To Enqueue Admin scripts.
	 *
	 * @param string $hook current screen name.
	 */
	public function enqueue_scripts( $hook ) {

		if ( ! empty( $hook ) && ( 'post.php' === $hook || 'post-new.php' === $hook ) &&
			wp_script_is( 'media-editor', 'enqueued' ) && // Checking this script for extra caution for media library, we are accessing is already loaded.
			wp_script_is( 'media-views', 'enqueued' ) &&
			wp_script_is( 'media-models', 'enqueued' )
		) {

			$file_extension = ( \PMC::is_production() ) ? '.min' : '';
			// Enqueue Script.
			wp_enqueue_script(
				'pmc_admin_media_validation_js',
				pmc_global_functions_url( sprintf( '/js/pmc-admin-media-validation%s.js', $file_extension ) ), // @codingStandardsIgnoreLine Using dynamic links only but getting flag on pipeline.
				array( 'jquery' ),
				false,
				true
			);

			// Enqueue Style.
			wp_enqueue_style(
				'pmc_admin_media_validation_css',
				pmc_global_functions_url( sprintf( '/css/pmc-admin%s.css', $file_extension ) ), // @codingStandardsIgnoreLine Using dynamic links only but getting flag on pipeline.
				array(),
				false,
				false
			);
		}
	}
}
