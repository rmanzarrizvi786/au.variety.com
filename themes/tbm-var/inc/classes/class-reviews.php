<?php
/**
 * Reviews
 *
 * Handler for Reviews functionality.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Review\Fields;
use PMC\Review\Review;
use PMC\Review\Json_Data;

/**
 * Class Reviews
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Reviews {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Initializes the theme assets.
	 *
	 * @since 2017.1.0
	 */
	protected function __construct() {

		if ( ! class_exists( '\PMC\Review\Review' ) ) {
			return;
		}

		add_filter( 'pmc_review_category_slugs', [ $this, 'review_categories' ] );
		add_filter( 'pmc_review_review_types', [ $this, 'modify_review_types_movies_only' ] );
		add_filter( 'pmc_review_review_type', [ $this, 'set_review_type_movies_only' ] );
		add_filter( 'pmc_review_review_image', [ $this, 'set_featured_image_for_review_image' ] );
		add_filter( 'pmc_review_is_valid_review_post', [ $this, 'is_film_review' ], 10, 2 );
		add_filter( 'pmc_review_review_data', [ $this, 'update_review_knowledge_panel' ], 10, 2 );
		add_filter( 'pmc_review_extra_fields', [ $this, 'pmc_review_extra_fields' ] );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 11 );
		add_action( 'custom_metadata_manager_init_metadata', array( $this, 'cast_and_crew_custom_fields' ) );
	}

	/**
	 * Categories which should allow review.
	 *
	 * @param array $categories List of categories.
	 *
	 * @return array
	 */
	public function review_categories( $categories = [] ) {

		return [ 'reviews' ];

	}

	/**
	 * Allows review only for movie.
	 *
	 * @param array $review_types List of review_types.
	 *
	 * @return array
	 */
	public function modify_review_types_movies_only( $review_types = [] ) {

		return [
			'movie-reviews' => esc_html__( 'Movie review', 'pmc-variety' ),
		];

	}

	/**
	 * Allow review-type only movie.
	 *
	 * @param string $review_type review_type.
	 *
	 * @return string
	 */
	public function set_review_type_movies_only( $review_type = '' ) {

		return ( empty( $review_type ) ) ? 'movie-reviews' : $review_type;

	}

	/**
	 * Cast And Crew Custom Fields
	 *
	 * Renders the Cast and Crew metabox on Reviews.
	 *
	 * Based heavily on code from the 2014 theme.
	 *
	 * @since 2017.1.0
	 * @action custom_metadata_manager_init_metadata
	 * @see pmc-variety-2014/functions.php
	 */
	public function cast_and_crew_custom_fields() {
		if ( ! function_exists( 'x_add_metadata_field' ) || ! function_exists( 'x_add_metadata_group' ) ) {
			return;
		}

		$grp_args = array(
			'label'    => __( 'Reviews - Cast and Crew data', 'pmc-variety' ),
			'context'  => 'normal',
		);

		$post_types = array( 'post' );

		// @codeCoverageIgnoreStart
		if ( class_exists( '\\PMC\Gallery\Defaults' ) ) {
			$post_types[] = \PMC\Gallery\Defaults::NAME;
		}
		// @codeCoverageIgnoreEnd

		x_add_metadata_group( 'variety_review_credit', $post_types, $grp_args );

		$mpaa_ratings = array(
			'na'    => 'N/A',
			'G'     => 'G',
			'PG'    => 'PG',
			'PG-13' => 'PG-13',
			'R'     => 'R',
			'NC-17' => 'NC-17',
		);

		x_add_metadata_field(
			'variety-review-origin',
			$post_types,
			array(
				'group'      => 'variety_review_credit',
				'field_type' => 'wysiwyg',
				'label'      => __( 'Origin', 'pmc-variety' ),
			)
		);

		x_add_metadata_field(
			'variety-secondary-credit',
			$post_types,
			array(
				'group'      => 'variety_review_credit',
				'field_type' => 'wysiwyg',
				'label'      => __( 'Crew / Creative', 'pmc-variety' ),
			)
		);

		x_add_metadata_field(
			'variety-review-mpaa-rating',
			$post_types,
			array(
				'group'      => 'variety_review_credit',
				'field_type' => 'select',
				'values'     => $mpaa_ratings,
				'label'      => __( 'MPAA Rating', 'pmc-variety' ),
			)
		);

		x_add_metadata_field(
			'variety-review-running-time',
			$post_types,
			array(
				'group'       => 'variety_review_credit',
				'field_type'  => 'text',
				'values'      => array(),
				'label'       => __( 'Running Time', 'pmc-variety' ),
				'description' => __( 'Please enter the running time in this format: "2 hours 23 minutes".', 'pmc-variety' ),
			)
		);

		x_add_metadata_field(
			'variety-primary-credit',
			$post_types,
			array(
				'group'      => 'variety_review_credit',
				'field_type' => 'wysiwyg',
				'label'      => __( 'Production', 'pmc-variety' ),
			)
		);

		x_add_metadata_field(
			'variety-primary-cast',
			$post_types,
			array(
				'group'      => 'variety_review_credit',
				'field_type' => 'wysiwyg',
				'label'      => __( 'Cast / With', 'pmc-variety' ),
			)
		);

		x_add_metadata_field(
			'variety-secondary-cast',
			array( 'post' ),
			array(
				'group'      => 'variety_review_credit',
				'field_type' => 'wysiwyg',
				'label'      => __( 'Secondary Cast', 'pmc-variety' ),
			)
		);

		x_add_metadata_field(
			'variety-music-by',
			array( 'post' ),
			array(
				'group'      => 'variety_review_credit',
				'field_type' => 'wysiwyg',
				'label'      => __( 'Music By', 'pmc-variety' ),
			)
		);
	}

	/**
	 * Get Rating
	 *
	 * Fetches the Rating post meta key.
	 *
	 * @since 2017.1.0
	 * @param object|int $post_obj A \WP_Post object or Post ID.
	 *
	 * @return bool|mixed A "rating" post meta value, else false.
	 */
	public function get_rating( $post_obj ) {
		if ( ! is_object( $post_obj ) && is_numeric( $post_obj ) ) {
			$post_obj = get_post( $post_obj );
		} elseif ( ! is_object( $post_obj ) ) {
			$post_obj = get_post();
		}

		$value = get_post_meta( $post_obj->ID, Fields::RATING, true );

		// Not using a straight empty() check as the value could be a form of zero.
		if ( '' !== $value && is_numeric( $value ) ) {
			return $value;
		}

		return false;
	}

	/**
	 * Get Cast and Crew Meta
	 *
	 * Fetches a field from the "Reviews - Cast and Crew data" metabox.
	 *
	 * Field labels have alternate names that can be used for readability.
	 *
	 * @since 2017.1.0
	 * @param string      $field The desired meta field.
	 * @param bool|string $default A default value.
	 * @param object|int  $post_obj A \WP_Post object or ID.
	 *
	 * @return mixed The field value, else the default.
	 */
	public function get_cast_and_crew_meta( $field, $default = false, $post_obj = 0 ) {
		if ( ! empty( $post_obj ) && is_numeric( $post_obj ) ) {
			$post_obj = get_post( $post_obj );
		} elseif ( empty( $post_obj ) ) {
			$post_obj = get_post();
		}

		// Some handy field name conversions.
		switch ( $field ) {
		    case 'origin' :
			    $field = 'variety-review-origin';
				break;
			case 'mpaa-rating' :
				$field = 'variety-review-mpaa-rating';
				break;
			case 'running-time' :
				$field = 'variety-review-running-time';
				break;
			case 'production' :
				$field = 'variety-primary-credit';
				break;
			case 'cast' :
				$field = 'variety-primary-cast';
				break;
			case 'secondary_cast' :
				$field = 'variety-secondary-cast';
				break;
			case 'crew' :
				$field = 'variety-secondary-credit';
				break;
			case 'music-by':
				$field = 'variety-music-by';
				break;
		}

		$value = get_post_meta( $post_obj->ID, $field, true );

		if ( ! is_wp_error( $value ) ) {
			return $value;
		}

		return $default;
	}

	/**
	 * Get Structured Data
	 *
	 * Returns an array of data for the Review structured data,
	 * pulled from the Film Review and Cast and Crew metaboxes.
	 *
	 * @return array
	 */
	public function get_review_structured_data() {
		$date = false;
		$date_value = $this->get_film_review_meta( Fields::RELEASE_DATE );
		if ( ! empty( $date_value ) && is_numeric( $date_value ) ) {
			$date = date( 'M j, Y', $date_value );
		}
		return array(
			'director'     => $this->get_film_review_meta( Fields::ARTIST ),
			'canonical'    => $this->get_film_review_meta( Fields::CANONICAL_LINK ),
			'cast'         => $this->get_cast_and_crew_meta( 'cast' ),
			'mpaa_rating'  => $this->get_cast_and_crew_meta( 'mpaa-rating' ),
			'running_time' => $this->get_cast_and_crew_meta( 'running-time' ),
			'release_date' => $date,
		);
	}

	/**
	 * Get Film Review Meta
	 *
	 * Fetches a value from the "Film Review" metabox created by
	 * the pmc-review plugin.
	 *
	 * @since 2017.1.0
	 * @see \PMC_Film_Review
	 * @param string $key The desired meta field.
	 * @param bool   $default Optional. A default value.
	 * @param int    $post_id Optional. A \WP_Post ID.
	 *
	 * @return bool|mixed The meta value, else the $default.
	 */
	public function get_film_review_meta( $key, $default = false, $post_id = 0 ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$value = get_post_meta( $post_id, $key, true );
		if ( ! empty( $value ) && ! is_wp_error( $value ) ) {
			return $value;
		}

		return $default;
	}

	/**
	 * Get the review data for display.
	 *
	 * @param object|int $post_obj A \WP_Post object or ID.
	 *
	 * @return array The review data.
	 */
	public function get_review_data( $post_obj = 0 ) {
		if ( ! empty( $post_obj ) && is_numeric( $post_obj ) ) {
			$post_obj = get_post( $post_obj );
		} elseif ( empty( $post_obj ) || ! is_object( $post_obj ) ) {
			$post_obj = get_post();
		}

		$vertical = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post_obj->ID, 'vertical' );

		if ( empty( $vertical->slug ) ) {
			return [];
		}

		$origin         = $this->get_cast_and_crew_meta( 'origin' );
		$production     = $this->get_cast_and_crew_meta( 'production' );
		$crew           = $this->get_cast_and_crew_meta( 'crew' );
		$primary_cast   = $this->get_cast_and_crew_meta( 'cast' );
		$secondary_cast = $this->get_cast_and_crew_meta( 'secondary_cast' );
		$music_by       = $this->get_cast_and_crew_meta( 'music-by' );
		$label_cast     = ( 'film' === $vertical->slug ) ? __( 'With', 'pmc-variety' ) : __( 'Cast', 'pmc-variety' );

		return [
			'origin'         => $origin,
			'production'     => $production,
			'crew'           => $crew,
			'primary_cast'   => $primary_cast,
			'secondary_cast' => $secondary_cast,
			'music_by'       => $music_by,
			'label_cast'     => $label_cast,
		];
	}

	/**
	 * Admin Enqueue Scripts
	 *
	 * Load scripts and styles in the WP Admin.
	 *
	 * @since 2017.1.0
	 * @action admin_enqueue_scripts, 11
	 *
	 * @param string $page The current page slug.
	 */
	public function admin_enqueue_scripts( $page ) {

		// Handle scripts for the pmc-film-review plugin.
		if ( 'post.php' === $page || 'post-new.php' === $page ) {

			// Exit this function for block editor posts.
			if ( apply_filters( 'pmc_review_block_editor_skip', false ) ) {
				return;
			}

			/*
			 * Register and enqueue legacy scripts to handle 'category', 'tag' for 'pmc-film-review' metabox.
			 */
			wp_register_script( 'variety-admin-single', get_stylesheet_directory_uri() . '/assets/build/js/admin_single.js', array( 'jquery' ) );
			wp_enqueue_script( 'variety-admin-single' );
		}
	}

	/**
	 * Use featured image as review image.
	 *
	 * @param string $review_image review image url.
	 *
	 * @return string
	 */
	public function set_featured_image_for_review_image( $review_image = '' ) {

		if ( is_single() && empty( $review_image ) ) {

			$post_id      = get_queried_object_id();
			$review_image = get_the_post_thumbnail_url( $post_id );

		}

		return $review_image;
	}

	/**
	 * Overrides Review::get_instance()->is_valid_review_post() for Variety only
	 *
	 * check to see if this post is a film review.
	 * A film review has to have a review snippet, a film name and a film canonical URL and release date
	 *
	 * @param $is_valid bool whether post is valid for review JSON data or not
	 * @param $post_id int   Current post ID
	 *
	 * @return bool True if the post is valid; otherwise false.
	 */
	public function is_film_review( $is_valid, $post_id = null ) {

		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$review_image         = Json_Data::get_instance()->get_review_image();
		$review_canonical_url = get_post_meta( $post_id, Fields::CANONICAL_LINK, true );

		return ( ! empty( $review_image )
				&& ! empty( Fields::get_instance()->get( Fields::SNIPPET, $post_id ) )
				&& ! empty( Fields::get_instance()->get( Fields::TITLE, $post_id ) )
				&& ! empty( $review_canonical_url )
		);

	}

	/**
	 * @param array  $review_data Array for review data
	 * @param string $review_type Type of review
	 *
	 * @return array
	 */
	public function update_review_knowledge_panel( $review_data, $review_type = '' ) {

		$rating = Fields::get_instance()->get( Fields::RATING );

		if ( is_array( $review_data ) && empty( $rating ) ) {
			unset( $review_data['reviewRating'] );
		}

		return $review_data;
	}

	/**
	 * @param array  $extra_fields Array for review data
	 *
	 * @return array Extra fields for review.
	 */
	public function pmc_review_extra_fields( $extra_fields ) {

		return [
			'cast'         => $this->get_cast_and_crew_meta( 'cast' ),
			'running_time' => $this->get_cast_and_crew_meta( 'running-time' ),
		];

	}

}
