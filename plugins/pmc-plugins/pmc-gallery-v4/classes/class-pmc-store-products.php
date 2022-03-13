<?php

namespace PMC\Gallery;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Integrations with the PMC Store Products plugin.
 */
class PMC_Store_Products {

	use Singleton;

	/**
	 * Initializes the class.
	 */
	public function __construct() {
		add_filter( 'pmc_store_products_displayed_content', [ $this, 'pmc_store_products_displayed_content' ] );
	}

	/**
	 * Include gallery content in what PMC Store Products inspects for shortcodes.
	 *
	 * @param string $content Existing content.
	 * @return string
	 */
	public function pmc_store_products_displayed_content( $content ) {
		require_once dirname( __DIR__ ) . '/classes/class-view.php';
		$gallery_data = View::fetch_gallery();
		if ( ! empty( $gallery_data ) ) {
			foreach ( $gallery_data as $gallery_slide ) {
				$content .= PHP_EOL . $gallery_slide['caption_no_shortcode'];
			}
		}
		return $content;
	}

}
