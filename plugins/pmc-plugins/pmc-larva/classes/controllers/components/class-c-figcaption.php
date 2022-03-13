<?php
/**
 * Controller for c-figcaption component.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Components;

use PMC\Global_Functions\Utility\Attachment;
use PMC\Larva\Controllers\Base;

/**
 * Class C_Figcaption.
 */
class C_Figcaption extends Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {
		if ( $this->_args['image_id'] ) {
			$data['c_figcaption_caption_markup'] = wp_get_attachment_caption(
				$this->_args['image_id']
			);

			$data['c_figcaption_credit_text'] = Attachment::get_instance()->get_image_credit(
				$this->_args['image_id']
			);
		} else {
			$hidden_class = ' lrv-a-hidden';

			$data['c_figcaption_caption_markup']   = '';
			$data['c_figcaption_caption_classes'] .= $hidden_class;

			$data['c_figcaption_credit_text']     = '';
			$data['c_figcaption_credit_classes'] .= $hidden_class;
		}
	}
}
