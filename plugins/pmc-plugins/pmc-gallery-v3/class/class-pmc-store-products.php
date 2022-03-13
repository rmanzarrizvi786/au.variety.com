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
		add_filter( 'pmc_gallery_data', [ $this, 'pmc_gallery_data' ] );
	}

	/**
	 * Applies shortcodes on 'caption' data in the gallery.
	 *
	 * @param array $gallery_data Existing gallery data.
	 * @return array
	 */
	public function pmc_gallery_data( $gallery_data ) {
		if ( ! empty( $gallery_data ) ) {
			foreach ( $gallery_data as $i => $gallery_slide ) {
				$gallery_data[ $i ]['caption'] = do_shortcode( $gallery_slide['caption'] );
			}
		}
		return $gallery_data;
	}
}
