<?php
/**
 * Homepage section widget (Lists, iHeartRadio)
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

/**
 * Class Section_Lists_IHeartRadio
 */
class Section_Lists_IHeartRadio extends Variety_Base_Widget {

	const ID = 'section-lists-iheartradio';

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety Section - Lists, iHeartRadio', 'pmc-variety' ),
			[
				'description' => __( 'Displays the Lists and iHeartRadio section', 'pmc-variety' ),
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

		if ( ! empty( $data['list_carousel'] ) ) {
			$data['articles']['lists'] = Carousels::get_carousel_posts( $data['list_carousel'], 3 );

		} else {
			$data['articles']['lists'] = [];
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
			'list_carousel'  => [
				'label'   => __( 'List Carousel', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			],
			'list_more_text' => [
				'label' => __( 'List More Link Text', 'pmc-variety' ),
				'type'  => 'text',
			],
			'list_more_link' => [
				'label' => __( 'List More Link URL', 'pmc-variety' ),
				'type'  => 'url',
			],
			'iframe'         => [
				'label' => __( 'iHeart Iframe URL', 'pmc-variety' ),
				'type'  => 'url',
			],
		);
	}
}
