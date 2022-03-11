<?php
/**
 * Awards Contenders widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

class Awards_Contenders extends Variety_Base_Widget {

	const ID = 'awards-contenders';

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
			__( 'Variety - Awards Contenders', 'pmc-variety' ),
			array( 'description' => __( 'Displays Awards Contenders Module.', 'pmc-variety' ) )
		);
	}

	/**
	 * Method which returns data to be displayed on the front-end.
	 * The data here is not cached & this method is not meant to be called directly.
	 *
	 * @param  array $data Widget data, from global data or overrides.
	 *
	 * @return mixed
	 */
	public function get_uncached_data( $data = array() ) {

		if ( ! empty( $data['awards_module'] ) ) {
			$data['articles'] = Carousels::get_carousel_posts( $data['awards_module'], $this->_count );
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
	 * @return array
	 */
	protected function get_fields() {

		$fields = array(
			'awards_heading' => array(
				'label' => __( 'Awards Module - Heading', 'pmc-variety' ),
				'type'  => 'text',
			),
			'awards_logline' => array(
				'label' => __( 'Awards Module - Logline', 'pmc-variety' ),
				'type'  => 'text',
			),
			'awards_module'  => array(
				'label'   => __( 'Awards Module', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			),
		);

		return $fields;
	}
}
