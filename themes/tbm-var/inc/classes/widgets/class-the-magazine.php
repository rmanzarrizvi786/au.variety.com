<?php
/**
 * The Magazine widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

/**
 * Class The_Magazine
 */
class The_Magazine extends Variety_Base_Widget {

	const ID = 'the-magazine';

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety - The Magazine', 'pmc-variety' ),
			[
				'description' => __( 'Displays recent magazine issues', 'pmc-variety' ),
			]
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
			$data['issues'] = Carousels::get_carousel_posts( $data['carousel'], 4 );

			for ( $i = 0; $i < count( $data['issues'] ); $i ++ ) {

				if ( empty( $data['issues'][ $i ]->image_id ) ) {
					$print_terms = wp_get_post_terms(
						$data['issues'][ $i ]->ID,
						'print-issues',
						[ 'fields' => 'ids' ]
					);

					if ( ! is_wp_error( $print_terms ) && ! empty( ( $print_terms[0] ) ) ) {
						$cover_image_id = get_term_meta( $print_terms[0], 'print-issue-image-id', true );
						if ( ! empty( $cover_image_id ) ) {
							$data['issues'][ $i ]->image_id = $cover_image_id;
						}
					}

				}
			}

		} else {
			$data['issues'] = [];
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
			'mobile_text' => array(
				'label' => __( 'Mobile Subscribe Text', 'pmc-variety' ),
				'type'  => 'text',
			),
			'more_text'   => array(
				'label' => __( 'More Link Text', 'pmc-variety' ),
				'type'  => 'text',
			),
			'more_link'   => array(
				'label' => __( 'More Link URL', 'pmc-variety' ),
				'type'  => 'url',
			),
			'carousel'    => array(
				'label'   => __( 'Carousel', 'pmc-variety' ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			),
		);
	}
}
