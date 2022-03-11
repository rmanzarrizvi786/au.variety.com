<?php
/**
 * Most Viewed widget
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc\Widgets;

class Most_Viewed extends Variety_Base_Widget {

	const ID = 'most-viewed';

	/**
	 * Count - How many posts to display.
	 *
	 * @var int
	 */
	protected $count = 3;

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety Sidebar - Most Viewed', 'pmc-variety' ),
			array( 'description' => __( 'Displays the most viewed articles', 'pmc-variety' ) )
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

		$days   = 7;
		$period = 1;

		if ( ! \PMC::is_production() ) {
			$days   = 300;
			$period = 300;
		}

		$data['articles'] = \PMC\Core\Inc\Top_Posts::get_posts( $this->count, $days, $period, 'most_viewed' );

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
			'count' => array(
				'label' => __( 'Number Of Articles', 'pmc-variety' ),
				'type'  => 'text',
			),
		);
	}
}
