<?php
/**
 * Controller for c-span component.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Components;

use PMC\Larva\Controllers\Base;

/**
 * Class C_Span.
 */
class C_Span extends Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {
		$data['c_span_text'] = $this->_args['text'];
		$data['c_span_url']  = $this->_args['url'];
	}
}
