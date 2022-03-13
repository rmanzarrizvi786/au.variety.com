<?php
/**
 * Controller for c-tagline-author component.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Components;

use PMC\Global_Functions\Utility\Author;
use PMC\Larva\Controllers\Base;

/**
 * Class C_Tagline_Author.
 */
class C_Tagline_Author extends Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {
		$data['c_tagline_text'] = '';

		$data['c_tagline_markup'] = (
			new Author( $this->_args['post_id'] )
		)->get_formatted();
	}
}
