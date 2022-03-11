<?php
/**
 * Class PMC_Disable_Getty_Images
 *
 * Configures/Customizes the PMC Disable Getty Images plugin
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Disable_Getty_Images {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 *
	 */
	protected function __construct() {

		// Remove filter for getty images using postmeta
		remove_filter(
			'ajax_query_attachments_args',
			[ \PMC_Disable_Getty_Images::get_instance(), 'filter_getty_images' ]
		);
		// Handle filtering of getty images in wp_prepare_attachment_for_js
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'wp_prepare_attachment_for_js' ] );
	}

	/**
	 * Filter out attachments that are getty images and should be prevented from being used.
	 *
	 * @param  $response Array of data for an attachment
	 *
	 * @return Array Data for attachment output
	 *
	 *
	 */
	public function wp_prepare_attachment_for_js( $response ) {

		// If the meta field exists, return a placeholder.
		if ( true === metadata_exists( 'post', intval( $response['id'] ), '_pmc_hide_in_media_library' ) ) {
			// We can't just remove the item because it breaks the attachment output and json encoding.
			// If the thumb field gets set, it becomes insertable. By sending out the data in this fashion,
			// it populates in the grid, but prevents inserting the image into the content
			return [
				'filename' => __(
					'This image is set to be hidden in media library and cannot be used. ',
					'pmc-variety'
				),
				'icon'     => $response['url'],
			];
		}

		return $response;
	}

}
//EOF
