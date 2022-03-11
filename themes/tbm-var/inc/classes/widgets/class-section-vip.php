<?php
/**
 * Homepage section widget (VIP)
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

/**
 * Class Section_VIP
 */
class Section_VIP extends Variety_Base_Widget {

	const ID = 'section-vip';

	const CACHE_LIFE = 0;     // no caching for this widget since the function already caches data.

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety Section - VIP', 'pmc-variety' ),
			[
				'description' => __( 'Displays the VIP section', 'pmc-variety' ),
			]
		);
	}

	/**
	 * Method which returns data to be displayed on the front-end.
	 * The data here is not cached & this method is not meant to be called directly.
	 *
	 * @param  array $data Widget data, from global data or overrides.
	 * @return mixed
	 *
	 */
	public function get_uncached_data( $data = [] ) {

		if ( ! empty( $data['count'] ) ) {
			$this->count = $data['count'];
		}

		if ( ! empty( $data['vip_carousel'] ) ) {
			$data['articles']['vip'] = Carousels::get_carousel_posts( $data['vip_carousel'], 5 );

		} else {
			$data['articles']['vip'] = [];
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
		return [
			'vip_carousel'  => array(
				'label'   => __( 'VIP Carousel', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			),
			'vip_more_text' => array(
				'label' => __( 'VIP More Link Text', 'pmc-variety' ),
				'type'  => 'text',
			),
			'vip_more_link' => array(
				'label' => __( 'VIP More Link URL', 'pmc-variety' ),
				'type'  => 'url',
			),
		];
	}
}
