<?php
/**
 * Coming Soon widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

class Coming_Soon extends Variety_Base_Widget {

	const ID = 'coming-soon';

	/**
	 * Count
	 *
	 * @var int How many posts to display.
	 */
	private $_count = 10;

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety - Coming Soon', 'pmc-variety' ),
			[ 'description' => __( 'Displays Coming Soon Module.', 'pmc-variety' ) ]
		);
	}

	/**
	 * Method which returns data to be displayed on the front-end.
	 * The data here is not cached & this method is not meant to be called directly.
	 *
	 * @param  array $data Widget data, from global data or overrides.
	 *
	 * @return array
	 */
	public function get_uncached_data( $data = [] ) {

		if ( ! empty( $data['coming_soon_module'] ) ) {
			$data['articles'] = Carousels::get_carousel_posts( $data['coming_soon_module'], $this->_count );
		} else {
			$data['articles'] = [];
		}

		return $data;
	}

	/**
	 * Get Fields.
	 *
	 * Get the option fields for this widget.
	 *
	 * @since 2017.1.0
	 * @return array
	 */
	protected function get_fields() {

		$fields = [
			'section_heading'    => [
				'label' => __( 'Coming Soon Module - Heading', 'pmc-variety' ),
				'type'  => 'text',
			],
			'section_logline'    => [
				'label' => __( 'Coming Soon Module - Logline', 'pmc-variety' ),
				'type'  => 'text',
			],
			'coming_soon_module' => [
				'label'   => __( 'Coming Soon Module', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			],
		];

		return $fields;
	}
}
