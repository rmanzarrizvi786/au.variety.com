<?php
/**
 * Controller for `c-select-option` component.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Components;

use PMC\Larva\Controllers\Base;

/**
 * Class C_Select_Option.
 */
class C_Select_Option extends Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {
		$data['c_select_option_text']       = $this->_args['text'];
		$data['c_select_option_url']        = $this->_args['url'];
		$data['c_select_option_value_attr'] = $this->_args['value']
			?? $this->_args['text'];
	}
}
