<?php
/**
 * Sharable Image functionality for PMC properties.
 *
 * @since 2019-09-24
 */

namespace PMC\Sharable_Images;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Setup
 */
class Setup {

	use Singleton;

	/**
	 * Hook our assets on the enqueue scripts action.
	 */
	protected function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'sharable_images_loader' ] );
	}

	/**
	 * Enqueue relevant scripts and styles.
	 */
	public function sharable_images_loader() {
		if ( is_single() ) {
			wp_enqueue_script( 'pmc_sharable_images', PMC_SHARABLE_IMAGES_URL . '/js/sharable-images.js', [ 'jquery' ], '1.0', true );
			wp_enqueue_style( 'pmc_sharable_images_style', PMC_SHARABLE_IMAGES_URL . '/css/style.css', false, '1.0' );
		}
	}
}
// EOF
