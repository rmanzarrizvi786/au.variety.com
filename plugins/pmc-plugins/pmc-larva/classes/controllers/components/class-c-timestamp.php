<?php
/**
 * Controller for c-timestamp component.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Components;

use PMC\Larva\Controllers\Base;

/**
 * Class C_Timestamp.
 */
class C_Timestamp extends Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {

		if (
			0 === $this->_args['post_id'] ||
			null === get_post( $this->_args['post_id'] )
		) {
			$data = false;
		} else {
			if ( ! empty( $this->_args['format'] ) ) {
				$data['c_timestamp_text'] = get_the_time(
					$this->_args['format'],
					$this->_args['post_id']
				);
			} else {
				$data['c_timestamp_text'] = pmc_human_time(
					get_the_time(
						'U',
						$this->_args['post_id']
					)
				);
			}

			$data['c_timestamp_datetime_attr'] = get_the_time(
				'Y-m-d H:i:s.uO',
				$this->_args['post_id']
			);
		}
	}
}
