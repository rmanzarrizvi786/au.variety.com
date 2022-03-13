<?php
/**
 * Larva Container module controller.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Modules;

use PMC\Larva\Controllers\Components;

class Carousel_Slider extends Base {

	/**
	 * Set up controller defaults.
	 */
	protected function __construct() {
		$this->pattern_shortpath = 'modules/carousel-slider';
	}

	/**
	 * Outline the default options structure for this module.
	 *
	 * This object is a structural contract for any data that is
	 * merged with the pattern object.
	 *
	 * @return array Array of default options that will be
	 *               merged with contextual data provided at the
	 *               template level.
	 */
	final public function get_default_options(): array {
		return [
			'data'    => [
				'posts' => [
					'ID' => 0,
				],
			],
			'variant' => 'prototype',
		];
	}

	/**
	 * Combine data with the pattern JSON object.
	 *
	 * @param array $pattern The Larva pattern JSON object to plugin data into.
	 * @param array $data    Actual data to override placeholder data, adhering to the
	 *                       structure outlined in get_default_options()['data'].
	 *
	 * @return array Object to utilmately be passed to render_template.
	 */
	public function populate_pattern_data( array $pattern, array $data ): array {

		$template = array_shift( $pattern['galleries'] );

		$pattern['galleries'] = [];

		foreach ( $data['posts'] as $post_data ) {

			$card_pattern = $template;

			$this->_populate_o_card(
				$card_pattern,
				$post_data
			);

			$pattern['galleries'][] = $card_pattern;

		}

		return $pattern;
	}

	protected function _populate_o_card( &$pattern, &$data ) {

		$pattern['o_card_link_url'] = $data['url'] ?? null;

		(
			new Components\C_Title(
				[
					'post_id' => $data['ID'],
					'title'   => $data['title'] ?? null,
				]
			)
		)->add_data( $pattern['c_title'] );

		if ( \PMC\Gallery\Defaults::NAME === get_post_type( $data['post_id'] ) ) {
			$images = get_post_meta( $data['post_id'], \PMC\Gallery\Defaults::NAME, true ) ?? [];

			$pattern['o_indicator']['c_span']['c_span_text'] = sprintf(
				/* translators: 1. Number of photos in gallery. */
				__(
					'%1$d Photos',
					'pmc-larva'
				),
				count( $images )
			);
		} else {
			$pattern['o_indicator'] = false;
		}

		(
			new Components\C_Lazy_Image(
				[
					'image_id' => $data['image_id'],
					'post_id'  => $data['post_id'],
				]
			)
		)->add_data( $pattern['c_lazy_image'] );

	}

}
