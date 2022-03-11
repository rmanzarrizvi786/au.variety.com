<?php
/**
 * Homepage section widget (VIP)
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

/**
 * Class Section_Awards
 */
class Section_Awards extends Variety_Base_Widget {

	const ID = 'section-awards';

	const CACHE_LIFE = 0;     // no caching for this widget since the function already caches data.

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety Section - Awards', 'pmc-variety' ),
			[
				'description' => __( 'Displays the Awards section', 'pmc-variety' ),
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

		if ( ! empty( $data['awards_carousel'] ) ) {
			$data['articles']['awards'] = Carousels::get_carousel_posts( $data['awards_carousel'], 5 );

		} else {
			$data['articles']['awards'] = [];
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
			'awards_carousel'  => [
				'label'   => __( 'Awards Carousel', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			],
			'awards_more_text' => [
				'label' => __( 'Awards More Link Text', 'pmc-variety' ),
				'type'  => 'text',
			],
			'awards_more_link' => [
				'label' => __( 'Awards More Link URL', 'pmc-variety' ),
				'type'  => 'url',
			],
		];
	}
}
