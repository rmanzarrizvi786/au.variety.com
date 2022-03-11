<?php
/**
 * Special Coverage widget
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

/**
 * Class Special_Coverage
 *
 * @since 2017.1.0
 * @see Global_Curateable
 */
class Special_Coverage extends Variety_Base_Widget {

	const ID = 'special_coverage';

	/**
	 * Count - How many posts to display.
	 *
	 * @var int
	 */
	private $count = 5;

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety Sidebar - Special Coverage', 'pmc-variety' ),
			array( 'description' => __( 'Displays special coverage articles', 'pmc-variety' ) )
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
	 *
	 */
	protected function get_fields() {
		return array(
			'title'     => array(
				'label' => __( 'Title', 'pmc-variety' ),
				'type'  => 'text',
			),
			'more_text' => array(
				'label' => __( 'More Link Text', 'pmc-variety' ),
				'type'  => 'text',
			),
			'more_link' => array(
				'label' => __( 'More Link URL', 'pmc-variety' ),
				'type'  => 'url',
			),
			'carousel'  => array(
				'label'   => __( 'Carousel', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			),
			'count'     => array(
				'label' => __( 'Number Of Articles', 'pmc-variety' ),
				'type'  => 'text',
			),
		);
	}
}
