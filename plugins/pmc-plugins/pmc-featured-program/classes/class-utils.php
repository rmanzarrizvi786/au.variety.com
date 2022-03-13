<?php
/**
 * Class holding featured program plugin utility methods.
 *
 * @package pmc-featured-program
 */

namespace PMC\Featured_Program;

use \PMC\Global_Functions\Traits\Singleton;

class Utils {

	use Singleton;

	/**
	 * Returns a populated `c_lazy_image` object.
	 *
	 * @param object $prototype Image prototype data.
	 * @param int    $id        ID of the image for which to retrieve data.
	 * @param string $size      Image size to retrieve.
	 * @param string $url       Value for the `c_lazy_image_link_url` key. Default `false`.
	 * @return mixed `false` if no image, or the populated prototype object.
	 */
	public function get_image_data( $prototype, $id, $size, $url = false ) {
		if ( ! $id ) {

			// Provide an empty placeholder in cases where an image ID is not provided.
			$image = [
				'c_lazy_image_placeholder_url' => $this->get_placeholder_img_url(),
				'c_lazy_image_srcset_attr'     => false,
				'c_lazy_image_sizes_attr'      => false,
				'c_lazy_image_alt_attr'        => '',
				'c_lazy_image_src_url'         => $this->get_placeholder_img_url(),
			];
		} else {
			
			$data = [
				'src'       => wp_get_attachment_image_url( $id, $size, false ),
				'image_alt' => \PMC::get_attachment_image_alt_text( $id ),
			];
			
			$image = [
				'c_lazy_image_placeholder_url' => ( $data && $data['src'] ) ? $data['src'] : $this->get_placeholder_img_url(),
				'c_lazy_image_srcset_attr'     => false,
				'c_lazy_image_sizes_attr'      => false,
				'c_lazy_image_alt_attr'        => $data ? $data['image_alt'] ?? '' : '',
				'c_lazy_image_src_url'         => ( $data && $data['src'] ) ? $data['src'] : $this->get_placeholder_img_url(),
			];
		}

		if ( $url ) {
			$image['c_lazy_image_link_url'] = $url;
		}

		// Use the actual image source as the "placeholder" when an AMP view.
		if ( \PMC::is_amp() ) {
			$image['o_figure']['c_lazy_image']['c_lazy_image_placeholder_url'] = $image['o_figure']['c_lazy_image']['c_lazy_image_src_url'];
		}

		return wp_parse_args( $image, $prototype );
	}

	/**
	 * Returns a populated `c_lazy_image` img markup.
	 *
	 * @param object $data Image data.
	 * @param object $data Image data.
	 * @return string The populated img markup.
	 */
	public function get_image_markup( $data, $classes = '' ) {
		
		$image = "<img src='%s' data-lazy-src='%s' alt='%s' data-lazy-srcset='%s' data-lazy-sizes='%s' class='c-lazy-image__img lrv-u-background-color-grey-lightest %s' />";
	
		return sprintf(
			$image,
			$data['c_lazy_image_placeholder_url'] ?? '',
			$data['c_lazy_image_src_url'] ?? '',
			$data['c_lazy_image_alt_attr'] ?? '',
			$data['c_lazy_image_srcset_attr'] ?? '',
			$data['c_lazy_image_sizes_attr'] ?? '',
			$classes
		);
	}


	/**
	 * Get placeholder image for lazy loading.
	 * @return string
	 */
	private function get_placeholder_img_url() {
		return PMC_FP_IMAGES_URL . 'lazyload-fallback.gif';
	}

	/**
	 * Get hed/dek from pmc-field-overrides plugin.
	 */ 
	public function get_field_overrides( $post ) {
		
		if ( empty( $post ) ) {
			$post = get_the_ID();
		}
		
		if ( empty( $post ) ) {
			return;
		}
		
		$meta_key = Config::get_instance()->prefix() . '_fp_description';
		
		return [
			'hed' => pmc_get_title( $post ),
			'dek' => get_post_meta( $post, $meta_key, true ),
		];

	}
		
	/**
	 * An admin helper to get the current post's parent category.
	 * Use on the post edit screen.
	 *
	 * @return int Category ID or -1.
	 */
	public function get_current_parent_category() {

		$post_id = \PMC::filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );

		if ( intval( $post_id ) < 1 ) {
			return -1;
		}
		
		$cat = get_post_meta( $post_id, 'categories', true );

		if ( is_numeric( $cat ) ) {
			return intval( $cat );
		}

		return -1;
	}

	/**
	 * Get Sponsor details for current post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array|void
	 */
	public function get_sponsor_data( $post_id = 0 ) {

		$post_id        = empty( $post_id ) ? get_the_ID() : $post_id;
		$meta_key       = Config::get_instance()->prefix() . '_sponsor_status';
		$sponsor_status = get_post_meta( $post_id, $meta_key, true );

		if ( 'true' !== $sponsor_status ) {
			return;
		}

		$meta_key           = Config::get_instance()->prefix() . '_sponsor_target_url';
		$sponsor_target_url = get_post_meta( $post_id, $meta_key, true );
		
		$meta_key     = Config::get_instance()->prefix() . '_sponsor_logo';
		$sponsor_logo = get_post_meta( $post_id, $meta_key, true );

		$sponsor_logo = wp_get_attachment_image_url( $sponsor_logo, 'full' );

		return [
			'status' => $sponsor_status,
			'link'   => $sponsor_target_url,
			'logo'   => $sponsor_logo,
		];
	}

}
