<?php
/**
 * Special Report widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

class Special_Report extends Variety_Base_Widget {

	const ID = 'special-report';

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety - Special Report', 'pmc-variety' ),
			array( 'description' => __( 'Displays a carousel of special report articles', 'pmc-variety' ) )
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
		if ( ! empty( $data['carousel'] ) ) {
			$data['articles'] = Carousels::get_carousel_posts( $data['carousel'], 4 );
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
	 * @return array
	 *
	 */
	protected function get_fields() {
		return array(
			'title'            => array(
				'label' => __( 'Title', 'pmc-variety' ),
				'type'  => 'text',
			),
			'mobile_sub_title' => array(
				'label' => __( 'Mobile Sub Title', 'pmc-variety' ),
				'type'  => 'text',
			),
			'mobile_image'     => array(
				'label' => __( 'Mobile Featured Image URL', 'pmc-variety' ),
				'type'  => 'text',
			),
			'more_text'        => array(
				'label' => __( 'More Link Text', 'pmc-variety' ),
				'type'  => 'text',
			),
			'more_link'        => array(
				'label' => __( 'More Link URL', 'pmc-variety' ),
				'type'  => 'url',
			),
			'carousel'         => array(
				'label'   => __( 'Carousel', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			),
		);
	}
}
