<?php
/**
 * Controller for c-dek component.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Components;

use PMC\Larva\Controllers\Base;

/**
 * Class C_Dek.
 */
class C_Dek extends Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {
		$data['c_dek_markup'] = '';

		if ( ! empty( $this->_args['excerpt'] ) ) {
			$data['c_dek_text'] = $this->_args['excerpt'];
		} else {
			$data['c_dek_text'] = get_the_excerpt(
				$this->_args['post_id']
			);
		}

		if ( empty( $data['c_dek_text'] ) ) {
			$data['c_dek_classes'] .= ' lrv-a-hidden';
		}
	}
}
