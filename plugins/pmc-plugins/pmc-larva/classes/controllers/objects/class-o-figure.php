<?php
/**
 * Controller for o-figure object.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Objects;

use PMC\Larva;

/**
 * Class O_Figure.
 */
class O_Figure extends Larva\Controllers\Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {
		$data['o_figure_link_url'] = get_permalink( $this->_args['post_id'] );

		Larva\add_controller_data(
			Larva\Controllers\Components\C_Figcaption::class,
			$this->_args,
			$data['c_figcaption']
		);

		Larva\add_controller_data(
			Larva\Controllers\Components\C_Lazy_Image::class,
			$this->_args,
			$data['c_lazy_image']
		);
	}
}
