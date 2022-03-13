<?php
/**
 * Fields class.
 *
 * @package pmc-review
 * @since 2018-05-11
 */

namespace PMC\Review;

use PMC\Global_Functions\Traits\Singleton;

class Fields {

	use Singleton;

	const RATING         = 'pmc-review-rating';
	const SNIPPET        = 'pmc-review-snippet';
	const TITLE          = 'pmc-review-title';
	const CANONICAL_LINK = 'pmc-review-canonical-link';
	const RELEASE_DATE   = 'pmc-theatrical-release-date';
	const ARTIST         = 'pmc-director';
	const REVIEW_TYPE    = 'pmc-review-type';
	const RATING_OUT_OF  = 'pmc-review-rating-out-of';
	const IMAGE          = 'pmc-review-image';

	/**
	 * Arguments to use when setting up the metabox.
	 *
	 * @var array
	 */
	public $group_args = array(
		'label'   => 'Review',
		'context' => 'normal',
	);

	/**
	 * The object context for the meta box.
	 *
	 * @var array
	 */
	public $post_array = array( 'post' );

	/**
	 * The name of the meta field group.
	 *
	 * @var string
	 */
	public $group_name = 'review-grp';

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Hooks into the custom metadata manager action.
	 */
	protected function _setup_hooks() {

		add_action( 'custom_metadata_manager_init_metadata', array( $this, 'set_up_meta_box' ) );

	}

	/**
	 * Provides settings for the metadata fields to register.
	 *
	 * @return array An array of arrays of args to pass to x_add_metadata_field.
	 */
	public function get_fields() {

		$fields = array(

			array(
				self::RATING,
				$this->post_array,
				array(
					'group'             => $this->group_name,
					'field_type'        => 'select',
					'values'            => $this->get_rating_values(),
					'label'             => __( 'Number of Stars', 'pmc-review' ),
					'sanitize_callback' => array( $this, 'sanitize_review_field' ),
				),
			),

			array(
				self::RATING_OUT_OF,
				$this->post_array,
				array(
					'group'             => $this->group_name,
					'field_type'        => 'select',
					'values'            => apply_filters( Review::FILTER_PREFIX . 'fields_rating_out_of_values', array(
						'5' => __( '5 (Movies and Other)', 'pmc-review' ),
					) ),
					'label'             => __( 'Out Of', 'pmc-review' ),
					'sanitize_callback' => array( $this, 'sanitize_review_field' ),
				),
			),

			array(
				self::REVIEW_TYPE,
				$this->post_array,
				array(
					'group'             => $this->group_name,
					'field_type'        => 'select',
					'values'            => $this->get_review_types(),
					'label'             => __( 'Review Type', 'pmc-review' ),
					'sanitize_callback' => array( $this, 'sanitize_review_field' ),
				),
			),

			array(
				self::SNIPPET,
				$this->post_array,
				array(
					'readonly'          => true,
					'group'             => $this->group_name,
					'field_type'        => 'textarea',
					'label'             => __( 'Review Snippet', 'pmc-review' ),
					'description'       => __( 'Use the visual editor\'s "Review Snippet" button to select text.', 'pmc-review' ),
					'sanitize_callback' => array( $this, 'sanitize_review_field' ),
				),
			),

			array(
				self::TITLE,
				$this->post_array,
				array(
					'group'             => $this->group_name,
					'label'             => '', // See get_field_descriptive_text.
					'description'       => '&nbsp;', // See get_field_descriptive_text.
					'sanitize_callback' => array( $this, 'sanitize_review_field' ),
				),
			),

			array(
				self::ARTIST,
				$this->post_array,
				array(
					'group'             => $this->group_name,
					'label'             => '', // See get_field_descriptive_text.
					'description'       => '&nbsp;', // See get_field_descriptive_text.
					'sanitize_callback' => array( $this, 'sanitize_review_field' ),
				),
			),

			array(
				self::CANONICAL_LINK,
				$this->post_array,
				array(
					'group'             => $this->group_name,
					'label'             => __( 'Film Canonical Link', 'pmc-review' ),
					'description'       => __( 'Enter the canonical link of the film being reviewed. You could enter either a link to an English Wikipedia page of the film, its IMDB page, or the official film website.', 'pmc-review' ),
					'sanitize_callback' => array( $this, 'sanitize_review_field' ),
				),
			),

			array(
				self::RELEASE_DATE,
				$this->post_array,
				array(
					'group'             => $this->group_name,
					'field_type'        => 'datepicker',
					'label'             => '', // See get_field_descriptive_text.
					'description'       => '&nbsp;', // See get_field_descriptive_text.
					'sanitize_callback' => array( $this, 'sanitize_review_field' ),
				),
			),

			array(
				self::IMAGE,
				$this->post_array,
				array(
					'group'             => $this->group_name,
					'field_type'        => 'upload',
					'label'             => '', // See get_field_descriptive_text.
					'description'       => '&nbsp;', // See get_field_descriptive_text.
					'sanitize_callback' => array( $this, 'sanitize_review_field' ),
				),
			),
		);

		/**
		 * Filters the review meta fields to be registerd.
		 *
		 * @param array An array of arrays of args to pass to x_add_metadata_field.
		 */
		return apply_filters( Review::FILTER_PREFIX . 'fields', $fields );
	}

	/**
	 * Adds the meta box.
	 */
	public function set_up_meta_box() {

		x_add_metadata_group( $this->group_name, $this->post_array, $this->group_args );

		foreach ( $this->get_fields() as $field_arguments ) {
			call_user_func_array( 'x_add_metadata_field', $field_arguments );
		}

	}

	/**
	 * Provides possible star ratings for the user to choose from.
	 *
	 * @return array An associative array of values.
	 */
	public function get_rating_values() {
		$rating_values = array(
			''    => '',
			'0.0' => '0.0',
			'0.5' => '0.5',
			'1.0' => '1.0',
			'1.5' => '1.5',
			'2.0' => '2.0',
			'2.5' => '2.5',
			'3.0' => '3.0',
			'3.5' => '3.5',
			'4.0' => '4.0',
			'4.5' => '4.5',
			'5.0' => '5.0',
		);

		/**
		 * Filters the available ratings or review.
		 *
		 * @param array The default ratings.
		 */
		return apply_filters( Review::FILTER_PREFIX . 'rating_values', $rating_values );
	}

	/**
	 * Provides review types for the user to select from.
	 *
	 * @return array An associative array of review types.
	 */
	public function get_review_types() {
		$review_types = array(
			''                    => '',
			'movie-reviews'       => __( 'Movie review', 'pmc-review' ),
			'music-album-reviews' => __( 'Music album review', 'pmc-review' ),
			'other-reviews'       => __( 'Other review (e.g., TV series or live performance)', 'pmc-review' ),
		);

		/**
		 * Filters the review types.
		 *
		 * @param array Default review types.
		 */
		return apply_filters( Review::FILTER_PREFIX . 'review_types', $review_types );
	}

	/**
	 * Provides an array of descriptive text to be localized in the plugin JavaScript file,
	 * allowing field labels and descriptions to be modified dynamically.
	 *
	 * @return array An associative array of descriptive text.
	 */
	public function get_field_descriptive_text() {
		$descriptive_text = array(
			self::ARTIST       => array(
				'movie-reviews'       => array(
					'label'       => __( 'Director', 'pmc-review' ),
					'description' => __( 'Enter the director of the film being reviewed.', 'pmc-review' ),
				),
				'music-album-reviews' => array(
					'label'       => __( 'Artist', 'pmc-review' ),
					'description' => __( 'Enter the artist who created the album being reviewed.', 'pmc-review' ),
				),
				'default'             => array(
					'label'       => __( 'Artist', 'pmc-review' ),
					'description' => __( 'Enter the artist responsible for the work being reviewed.', 'pmc-review' ),
				),
			),

			self::TITLE        => array(
				'movie-reviews'       => array(
					'label'       => __( 'Film Name', 'pmc-review' ),
					'description' => __( 'Enter the name of the film being reviewed.', 'pmc-review' ),
				),
				'music-album-reviews' => array(
					'label'       => __( 'Album Title', 'pmc-review' ),
					'description' => __( 'Enter the name of the album being reviewed.', 'pmc-review' ),
				),
				'default'             => array(
					'label'       => __( 'Review Subject', 'pmc-review' ),
					'description' => __( 'Enter the name of the work being reviewed.', 'pmc-review' ),
				),
			),

			self::RELEASE_DATE => array(
				'movie-reviews'       => array(
					'label'       => __( 'Theatrical Release Date', 'pmc-review' ),
					'description' => __( 'Select the film\'s release date.', 'pmc-review' ),
				),
				'music-album-reviews' => array(
					'label'       => __( 'Release Date', 'pmc-review' ),
					'description' => __( 'Enter the album\'s release date.', 'pmc-review' ),
				),
			),

			self::IMAGE        => array(
				'movie-reviews'       => array(
					'label'       => __( 'Film Image', 'pmc-review' ),
					'description' => __( 'Upload an image from the film.', 'pmc-review' ),
				),
				'music-album-reviews' => array(
					'label'       => __( 'Album Cover Image', 'pmc-review' ),
					'description' => __( 'Upload the album cover image.', 'pmc-review' ),
				),
				'default'             => array(
					'label'       => __( 'Review Image', 'pmc-review' ),
					'description' => __( 'Upload an image representing the review subject.', 'pmc-review' ),
				),
			),
		);

		/**
		 * Filters the meta field descriptive text.
		 *
		 * @param array An associative array of descriptive text.
		 */
		return apply_filters( Review::FILTER_PREFIX . 'descriptive_text', $descriptive_text );
	}

	/**
	 * Sanitize the field value before saving.
	 *
	 * @param string $field_slug The field slug.
	 * @param array $field Unused
	 * @param array $object_type Unused.
	 * @param int $object_id Unused.
	 * @param mixed $value The updated value.
	 *
	 * @return mixed|string
	 */
	public function sanitize_review_field( $field_slug, $field, $object_type, $object_id, $value ) {
		switch ( $field_slug ) {
			case self::IMAGE:
			case self::CANONICAL_LINK:
				return esc_url( $value );

			case self::SNIPPET:
				return \PMC::truncate( $value, 200 );

			case self::RELEASE_DATE:
				return intval( $value );
		}

		return sanitize_text_field( $value );
	}

	/**
	 * Retrieves a meta value.
	 *
	 * @param string $key A meta key.
	 * @param null|int $post_id A WP_Post ID.
	 * @return mixed The meta value.
	 */
	public function get( $key, $post_id = null ) {
		static $cache = [];

		$post_id = null !== $post_id ? $post_id : get_the_ID();

		if ( ! isset( $cache[ "$key$post_id" ] ) ) {
			$cache[ "$key$post_id" ] = get_post_meta( $post_id, $key, true );
		}

		return $cache[ "$key$post_id" ];
	}
}
