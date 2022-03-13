<?php
/**
 * Controller for c-title component.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Components;

use PMC\Larva;

/**
 * Class C_Title.
 */
class C_Title extends Larva\Controllers\Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {
		if ( ! empty( $this->_args['title'] ) ) {
			$data['c_title_text'] = $this->_args['title'];
		} elseif ( ! empty( $this->_args['post_id'] ) ) {
			$data['c_title_text'] = get_the_title(
				$this->_args['post_id']
			);
		}

		if ( ! empty( $this->_args['url'] ) ) {
			$data['c_title_url'] = $this->_args['url'];
		} elseif ( ! empty( $this->_args['post_id'] ) ) {
			$data['c_title_url'] = get_permalink( $this->_args['post_id'] );
		}

		if ( ! empty( $this->_args['post_id'] ) ) {
			$data['c_title_id_attr'] = Larva\get_id_attribute_for_post_id(
				$this->_args['post_id']
			);
		} else {
			$data['c_title_id_attr'] = uniqid( 'c-title-', true );
		}
	}
}
