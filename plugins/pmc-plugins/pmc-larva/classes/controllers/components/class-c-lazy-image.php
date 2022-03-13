<?php
/**
 * Controller for c-lazy-image component.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Components;

use PMC;
use PMC\Core\Inc\Media;
use PMC\Larva\Controllers\Base;

/**
 * Class C_Lazy_Image.
 */
class C_Lazy_Image extends Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {
		$image_size = $this->_args['image_size'] ?? 'large';

		if ( $this->_args['image_id'] ) {
			$data['c_lazy_image_src_url']     =
				wp_get_attachment_image_url(
					$this->_args['image_id'],
					$image_size
				);
			$data['c_lazy_image_srcset_attr'] =
				wp_get_attachment_image_srcset(
					$this->_args['image_id'],
					$image_size
				);
			$data['c_lazy_image_sizes_attr']  =
				wp_get_attachment_image_sizes(
					$this->_args['image_id'],
					$image_size
				);
			$data['c_lazy_image_alt_attr']    =
				PMC::get_attachment_image_alt_text(
					$this->_args['image_id'],
					$this->_args['post_id'] ?? null
				);
		} else {
			$data['c_lazy_image_src_url']         = false;
			$data['c_lazy_image_srcset_attr']     = false;
			$data['c_lazy_image_sizes_attr']      = false;
			$data['c_lazy_image_alt_attr']        = false;
			$data['c_lazy_image_placeholder_url'] = false;
		}

		if ( class_exists( Media::class, false ) ) {
			// Cannot expect class as it comes from `pmc-core-v2`.
			// @codeCoverageIgnoreStart
			$data['c_lazy_image_placeholder_url'] = Media::get_instance()
				->get_placeholder_img_url();
			// @codeCoverageIgnoreEnd
		}

		if ( ! empty( $this->_args['url'] ) ) {
			$data['c_lazy_image_link_url'] = $this->_args['url'];
		} elseif ( ! empty( $this->_args['post_id'] ) ) {
			$data['c_lazy_image_link_url'] =
				get_permalink(
					$this->_args['post_id']
				);
		} else {
			$data['c_lazy_image_link_url'] = '#';
		}
	}
}
