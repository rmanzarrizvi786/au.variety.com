<?php
/**
 * What To Watch widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

class Stories_Row extends Variety_Base_Widget {

	const ID = 'stories-row';

	/**
	 * Count
	 *
	 * @var int How many posts to display.
	 */
	private $_count = 5;

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety - Stories Row', 'pmc-variety' ),
			array( 'description' => __( 'Display row of 3 stories.', 'pmc-variety' ) )
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
		if ( ! empty( $data['stories_carousel'] ) ) {
			$data['articles'] = Carousels::get_carousel_posts( $data['stories_carousel'], $this->_count );
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
			'stories_carousel' => array(
				'label'   => __( 'Carousel', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			),
		);

		return $fields;
	}
}
