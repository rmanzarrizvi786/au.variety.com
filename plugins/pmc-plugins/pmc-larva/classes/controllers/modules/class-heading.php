<?php
/**
 * Larva Heading module controller.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Modules;

class Heading extends Base {

	/**
	 * Set up controller defaults.
	 */
	protected function __construct() {
		$this->pattern_shortpath = 'modules/heading';
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
				'styles'        => [
					'text_align'       => '',
					'background_color' => '',
					'color'            => '',
				],
				'inner_html'    => '',
				'heading_level' => '',
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

		$pattern['heading_markup']     = $data['inner_html'];
		$pattern['heading_level_text'] = $data['heading_level'];

		foreach ( $data['styles'] as $key => $value ) {
			if ( isset( $value ) ) {
				$pattern[ 'heading_' . $key . '_class' ] = $value;
			}
		}

		return $pattern;
	}

}
