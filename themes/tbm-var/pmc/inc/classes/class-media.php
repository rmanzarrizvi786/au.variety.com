<?php
/**
 * PMC Core media setup.
 *
 * @package pmc-core-v2
 *
 * @since   2018-12-18
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;

class Media {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * Initializes hooks.
	 */
	protected function _setup_hooks() {

		add_filter( 'image_size_names_choose', [ $this, 'image_size_choose_media_manager' ] );
		add_filter( 'pmc_image_size_warning', [ $this, 'image_size_warnings' ] );
		add_filter( 'init', [ $this, 'set_lazy_load' ], 9 );
	}

	/**
	 * On Media manager while inserting post show only custom defined size in this function to be inserted on post.
	 *
	 * @param $sizes
	 *
	 * @return array
	 */
	public function image_size_choose_media_manager( $sizes ) {

		$sizes = [
			'horizontal' => __( 'Horizontal', 'pmc-core' ),
			'vertical'   => __( 'Vertical', 'pmc-core' ),
			'large'      => __( 'Large', 'pmc-core' ),
			'full'       => __( 'Full', 'pmc-core' ),
		];

		return $sizes;
	}

	/**
	 * Get attachment data like url, credits, caption and alt text.
	 *
	 * @param int|Object $post_obj   Post object OR int post id.
	 * @param string     $image_size Attachment size.
	 *
	 * @return array
	 */
	public function get_image_data_by_post( $post_obj, $image_size = 'thumbnail' ) {

		$post_obj = ( is_numeric( $post_obj ) ) ? get_post( $post_obj ) : $post_obj;

		if ( has_post_thumbnail( $post_obj->ID ) ) {

			return $this->get_image_data( get_post_thumbnail_id( $post_obj ), $image_size );
		}

		return [];
	}

	/**
	 * Get attachment data like url, credits, caption and alt text.
	 *
	 * @param int    $image_id   Attachment ID.
	 * @param string $image_size Attachment size.
	 *
	 * @return array|bool
	 */
	public function get_image_data( $image_id, $image_size = 'thumbnail' ) {

		if ( empty( $image_id ) ) {
			return false;
		}

		$data = [
			'src'           => wp_get_attachment_image_url( $image_id, $image_size, false ),
			'image_credit'  => \pmc_get_photo_credit( $image_id ),
			'image_caption' => wp_get_attachment_caption( $image_id ),
			'image_alt'     => \PMC::get_attachment_image_alt_text( $image_id ),
		];

		return $data;

	}

	/**
	 * Add warning for Featured Images less then certain dimensions
	 *
	 * @param array $images Images array.
	 *
	 * @return array
	 */
	public function image_size_warnings( $images = [] ) {

		$images[] = [
			'title'   => 'Featured Image',
			'width'   => 910,
			'height'  => 511,
			'message' => 'Image is too small for a Featured Image: 617x414',
		];

		return $images;
	}

	/**
	 * Get placeholder image for lazy loading.
	 * @return string
	 */
	public function get_placeholder_img_url() {

		$placeholder_img_url = CHILD_THEME_URL . '/assets/public/lazyload-fallback.jpg';

		return apply_filters( 'pmc_core_placeholder_img_url', $placeholder_img_url );
	}

	/**
	 * Setup lazy loading
	 */
	public function set_lazy_load() {

		add_filter( 'lazyload_is_enabled', '__return_true', 11 );

		//dont have option other then checking the url path since is_page wont work on init
		// and to disable it on results page, we need it before init runs
		$url = $_SERVER['REQUEST_URI']; // phpcs:ignore

		$path = wp_parse_url( $url, PHP_URL_PATH );

		$path = sanitize_text_field( $path );

		if ( '/results/' === $path ) {
			add_filter( 'lazyload_is_enabled', '__return_false', 12 );
		}
	}

}
//EOF
