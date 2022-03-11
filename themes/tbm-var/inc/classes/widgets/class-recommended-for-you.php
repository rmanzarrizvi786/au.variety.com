<?php
/**
 * Recommended For You widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

class Recommended_For_You extends Variety_Base_Widget {

	const ID = 'recommended-for-you';

	/**
	 * Count - How many posts to display.
	 *
	 * @var int
	 */
	private $count = 8;

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety - Recommended For You', 'pmc-variety' ),
			array( 'description' => __( 'Displays a carousel of recommended articles', 'pmc-variety' ) )
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
	 *
	 */
	public function get_uncached_data( $data = array() ) {

		if ( ! empty( $data['count'] ) ) {
			$this->count = $data['count'];
		}

		if ( ! empty( $data['carousel'] ) ) {
			$data['articles'] = Carousels::get_carousel_posts( $data['carousel'], $this->count );
		} else {
			$data['articles'] = array();
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
	 *
	 */
	protected function get_fields() {
		return array(
			'carousel' => array(
				'label'   => __( 'Carousel', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			),
		);
	}
}
