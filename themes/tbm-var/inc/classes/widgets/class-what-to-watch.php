<?php
/**
 * What To Watch widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

class What_To_Watch extends Variety_Base_Widget {

	const ID = 'what-to-watch';

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
			__( 'Variety - What To Watch', 'pmc-variety' ),
			array( 'description' => __( 'Displays hub of recommendations from streaming service.', 'pmc-variety' ) )
		);
	}

	/**
	 * Method which returns data to be displayed on the front-end.
	 * The data here is not cached & this method is not meant to be called directly.
	 *
	 * @since 2017-10-04 Amit Gupta - CDWE-702
	 *
	 * @param  array $data Widget data, from global data or overrides.
	 * @return mixed
	 */
	public function get_uncached_data( $data = array() ) {

		if ( ! empty( $data['stream_module'] ) ) {
			$data['articles'] = Carousels::get_carousel_posts( $data['stream_module'], $this->_count );
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

		$fields = array(
			'stream_heading' => array(
				'label' => __( 'Stream Module 1 - Heading', 'pmc-variety' ),
				'type'  => 'text',
			),
			'stream_module'  => array(
				'label'   => __( 'Stream Module', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			),
		);

		return $fields;
	}
}
