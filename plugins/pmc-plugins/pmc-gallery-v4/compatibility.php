<?php
/**
 * Contains deprecated classes
 *
*/

/**
 * PMC_Gallery_Common has been replaced with PMC\Gallery\Defaults
 *
 * @deprecated
 */
class PMC_Gallery_Common {

	const KEY = 'pmc-gallery';

}

/**
 * Adaeze Esiobu @ since 04-01-2014
 *
 * Backwards compatibility: PMC_Gallery_Thefrontend has most of its functionality replaced by \PMC\Gallery\View
 *
 * @deprecated
 */
class PMC_Gallery_Thefrontend {

	/**
	 * Load Gallery
	 *
	 * @param int $gallery Gallery ID.
	 * @param int $linked_gallery Linked gallery id.
	 *
	 * @deprecated
	 * @see \PMC\Gallery\View::load_gallery
	 *
	 * @return mixed
	 */
	public static function load_gallery( $gallery = null, $linked_gallery = null ) {
		return \PMC\Gallery\View::get_instance()->load_gallery( $gallery, $linked_gallery );
	}

	/**
	 * Get linked gallery.
	 *
	 * @param int $post_id Post id.
	 *
	 * @deprecated
	 * @see \PMC\Gallery\View::get_linked_gallery_data
	 *
	 * @return array
	 */
	public static function get_linked_gallery_data( $post_id ) {
		return \PMC\Gallery\View::get_linked_gallery_data( $post_id );
	}

}

class_alias( 'PMC\Gallery\Defaults', 'PMC_Gallery_Defaults' );

if ( ! is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	class_alias( 'PMC\Gallery\View', 'PMC_Gallery_View' );
}
