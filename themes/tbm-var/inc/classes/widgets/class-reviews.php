<?php
/**
 * Reviews Widget
 *
 * Handler for the Reviews Widget on the Homepage.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

/**
 * Class Reviews
 *
 * @since 2017.1.0
 * @see Singleton
 */
class Reviews extends Variety_Base_Widget {

	const ID = 'reviews';

	/**
	 * Count
	 *
	 * @var int How many posts to display.
	 */
	private $_count = 4;

	/**
	 * The Verticals to be included in this widget.
	 *
	 * @var array Array of Vertical slugs.
	 */
	public $verticals = array(
		'tv',
		'film',
		'music',
		'legit',
	);

	/**
	 * Register widget with WordPress
	 *
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety - Reviews', 'pmc-variety' ),
			array( 'description' => __( 'Displays curated reviews for Film, TV, Legit, and Music.', 'pmc-variety' ) )
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
			$this->_count = $data['count'];
		}

		foreach ( $this->verticals as $vertical ) {
			if ( ! empty( $data[ 'carousel_' . $vertical ] ) ) {
				$vertical_filter = function() {
					return 'vertical';
				};

				$term_filter = function() use ( $vertical ) {
					return $vertical;
				};

				$category_filter = function ( $args ) {
					$args['category_name'] = 'reviews';
					return $args;
				};

				add_filter( 'pmc_carousel_items_fallback_taxonomy', $vertical_filter, 10, 0 );
				add_filter( 'pmc_carousel_items_fallback_term', $term_filter, 10, 0 );
				add_filter( 'pmc_carousel_items_fallback_args', $category_filter );

				$data['articles'][ $vertical ] = Carousels::get_carousel_posts( $data[ 'carousel_' . $vertical ], $this->_count, false, 'taxonomy' );

				remove_filter( 'pmc_carousel_items_fallback_taxonomy', $vertical_filter );
				remove_filter( 'pmc_carousel_items_fallback_term', $term_filter );
				remove_filter( 'pmc_carousel_items_fallback_args', $category_filter );
			} else {
				$data['articles'][ $vertical ] = [];
			}
		}

		return $data;

	}

	/**
	 * Get Fields.
	 *
	 * Get the option fields for this widget.
	 *
	 * There is no "count" option, as 4 reviews
	 * are required to always display.
	 *
	 * @since 2017.1.0
	 *
	 * @return array
	 *
	 */
	protected function get_fields() {
		$fields = array(
			'title'     => array(
				'label' => __( 'Title', 'pmc-variety' ),
				'type'  => 'text',
			),
			'more_text' => array(
				'label' => __( 'More Reviews Link Text', 'pmc-variety' ),
				'type'  => 'text',
			),
			'more_link' => array(
				'label' => __( 'More Link URL', 'pmc-variety' ),
				'type'  => 'url',
			),
		);

		foreach ( $this->verticals as $vertical ) {
			$term = get_term_by( 'slug', $vertical, 'vertical' );
			if ( empty( $term->name ) || is_wp_error( $term ) ) {
				continue;
			}
			$fields[ 'carousel_' . $vertical ] = array(
				'label'   => sprintf( __( '%s Carousel', 'pmc-variety' ), $term->name ),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			);
		}

		return $fields;
	}
}
