<?php
/**
 * Controller for c-link component.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Components;

use PMC\Larva\Controllers\Base;

/**
 * Class C_Link.
 */
class C_Link extends Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {
		$data['c_link_text'] = $this->_args['text'];
		$data['c_link_url']  = $this->_args['url'];
	}
}
