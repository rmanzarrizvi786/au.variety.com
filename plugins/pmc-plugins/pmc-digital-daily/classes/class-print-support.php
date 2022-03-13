<?php
/**
 * Print-specific functionality.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Print_Support.
 */
class Print_Support {
	use Singleton;

	/**
	 * Retrieve print URL for given issue.
	 *
	 * @param int $post_id Post ID.
	 * @return string|null
	 */
	public static function get_url( int $post_id ): ?string {
		$pdf_id = Meta::get_print_pdf_id( $post_id );

		if ( null === $pdf_id ) {
			return null;
		}

		$url = wp_get_attachment_url( $pdf_id );

		return empty( $url ) ? null : $url;
	}
}
