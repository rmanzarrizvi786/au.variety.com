<?php
/**
 * Larva Button module controller.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Modules;

class Button extends Base {

	/**
	 * Set up controller defaults.
	 */
	protected function __construct() {
		$this->pattern_shortpath = 'modules/button';
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
				'styles'     => [
					'background_color' => '',
					'color'            => '',
				],
				'url'        => '',
				'inner_html' => '',
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

		$pattern['button_url']    = $data['url'];
		$pattern['button_markup'] = $data['inner_html'];

		// Note: This is temporary until we support alignment in the Buttons block.
		$pattern['button_classes'] = $pattern['button_classes'] . ' lrv-u-display-table lrv-u-margin-lr-auto';

		foreach ( $data['styles'] as $key => $value ) {
			if ( isset( $value ) ) {
				$pattern[ 'button_' . $key . '_class' ] = $value;
			}
		}

		return $pattern;
	}

}
