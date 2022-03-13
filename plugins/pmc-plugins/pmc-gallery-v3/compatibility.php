<?php
/**
 * This file should hold classes and methods that may have changed location or name with the redesign
 * having this will allow pmc-gallery to be backwards compatible.
 */
/**
 * functionality from PMC_Gallery_Common has been replaced with PMC_Gallery_Defaults
 */
class PMC_Gallery_Common {
	const KEY = 'pmc-gallery';

}

/**
 * Adaeze Esiobu @ since 04-01-2014
 * Backwards compatibility: PMC_Gallery_Thefrontend has most of its functionality replaced by PMC_Gallery_View
 */
class PMC_Gallery_Thefrontend {

	/**
	 * @deprecated
	 * @param null $gallery
	 * @param null $linked_gallery
	 * This function is placed for backward compatibility. Please do not use this class or method
	 * for newer development.
	 */
	public static function load_gallery( $gallery = null, $linked_gallery = null ) {
		return PMC_Gallery_View::get_instance()->load_gallery( $gallery, $linked_gallery );
	}

	/**
	 * @deprecated
	 * @see PMC_Gallery_View::get_linked_gallery_data
	 * This function is placed for backward compatibility. Please do not use this class or method
	 * for newer development.
	 */
	public static function get_linked_gallery_data( $post_id ) {
		return PMC_Gallery_View::get_linked_gallery_data( $post_id );
	}

}

