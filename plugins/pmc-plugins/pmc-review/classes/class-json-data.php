<?php
/**
 * Json_Data class.
 *
 * @package pmc-review
 * @since 2018-05-11
 */

namespace PMC\Review;

use PMC\Global_Functions\Traits\Singleton;

class Json_Data {

	use Singleton;

	/**
	 * The review type.
	 *
	 * @var null|\WP_Term
	 */
	public $review_type = null;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Adds hook callbacks.
	 */
	protected function _setup_hooks() {

		add_action( 'wp_head', array( $this, 'render_review_knowledge_panel' ) );
		add_filter( Review::FILTER_PREFIX . 'movie-reviews_data', array( $this, 'filter_movie_fields' ) );
		add_filter( Review::FILTER_PREFIX . 'music-album-reviews_data', array( $this, 'filter_music_album_fields' ) );

	}

	/**
	 * Generates the JSON data and sends it to the page.
	 */
	public function render_review_knowledge_panel() {

		if ( ! is_single() || ! Review::get_instance()->is_valid_review_post( get_the_ID() ) ) {
			return;
		}

		$this->review_type = $this->get_review_type();

		$review_data = array(
			'@context'      => 'http://schema.org',
			'@type'         => 'Review',
			'datePublished' => get_post_time( 'c', true ),
			'description'   => $this->get_review_snippet(),
			'url'           => get_permalink(),
			'author'        => $this->get_author(),
			'itemReviewed'  => array(
				'@type' => 'Thing',
				'name'  => $this->get_review_name(),
				'image' => $this->get_review_image(),
			),
			'reviewRating'  => array(
				'@type'       => 'Rating',
				'worstRating' => 0,
				'bestRating'  => Fields::get_instance()->get( Fields::RATING_OUT_OF ),
				'ratingValue' => Fields::get_instance()->get( Fields::RATING ),
			),
			'publisher'     => array(
				'@type'  => 'Organization',
				'name'   => $this->get_review_publisher_name(),
				'sameAs' => $this->get_review_publisher_url(),
			),
		);

		/**
		 * Filters the review data.
		 *
		 * @param array Data shared by all review types.
		 */
		$review_data = apply_filters( Review::FILTER_PREFIX . 'review_data', $review_data, $this->review_type );

		/**
		 * Filters the review data based on the review_type.
		 *
		 * @param array Data shared by all review types.
		 */
		$review_data = apply_filters( Review::FILTER_PREFIX . "{$this->review_type}_data", $review_data );

		/**
		 * Filters the ID attribute applied to the JSON script tag.
		 *
		 * @param string The ID. Default: 'pmc-review-snippet'.
		 */
		$json_element_id = apply_filters( Review::FILTER_PREFIX . 'json_id', 'pmc-review-snippet' );

		$script_options = array(
			'object_only'           => true,
			'script_tag_attributes' => array(
				'type' => 'application/ld+json',
				'id'   => $json_element_id,
			),
		);

		\PMC_Scripts::add_script( 'pmc_review', $review_data, 'wp_footer', 10, $script_options );

	}

	/**
	 * Sanitizes a string for JSON output.
	 *
	 * @param string $string A string to sanitize.
	 * @return string The sanitized string.
	 */
	public function sanitize_json_string_value( $string = '' ) {

		$string = \PMC::untexturize( $string );
		$string = html_entity_decode( $string, ENT_QUOTES );
		$string = wp_strip_all_tags( $string );

		return $string;
	}

	/**
	 * Provides the review publisher name.
	 *
	 * @return string Publisher name.
	 */
	public function get_review_publisher_name() {
		$publisher_name = get_bloginfo( 'name', 'display' );

		/**
		 * Filters the review publisher name.
		 *
		 * @param string The publisher name.
		 */
		return apply_filters( 'pmc_review_publisher_name', $publisher_name );
	}

	/**
	 * Provides the review publisher URL.
	 *
	 * @return string Publisher URL.
	 */
	public function get_review_publisher_url() {
		$publisher_url = get_home_url();

		/**
		 * Filters the review publisher URL.
		 *
		 * @param string The publisher URL.
		 */
		return apply_filters( 'pmc_review_publisher_url', $publisher_url );
	}


	/**
	 * Gets the review snippet.
	 *
	 * @return string The review snippet.
	 */
	public function get_review_snippet() {
		$review_snippet = Fields::get_instance()->get( Fields::SNIPPET );
		$review_snippet = $this->sanitize_json_string_value( $review_snippet );

		/**
		 * Filters a review snippet.
		 *
		 * @param string The review snippet.
		 * @param string The review type.
		 */
		return apply_filters( Review::FILTER_PREFIX . 'review_snippet', $review_snippet, $this->review_type );
	}

	/**
	 * Gets a review name.
	 *
	 * @return string The review name.
	 */
	public function get_review_name() {
		$review_name = Fields::get_instance()->get( Fields::TITLE );
		$review_name = $this->sanitize_json_string_value( $review_name );

		/**
		 * Filters the review name.
		 *
		 * @param string The review name.
		 * @param string The review type.
		 */
		return apply_filters( Review::FILTER_PREFIX . 'review_name', $review_name, $this->review_type );
	}

	/**
	 * Gets a review artist.
	 *
	 * @return string The review artist.
	 */
	public function get_review_artist() {
		$review_artist = Fields::get_instance()->get( Fields::ARTIST );
		$review_artist = ! empty( $review_artist ) ? $review_artist : '';

		/**
		 * Filters a review artist.
		 *
		 * @param string The review artist.
		 * @param string The review type.
		 */
		return apply_filters( Review::FILTER_PREFIX . 'review_artist', $review_artist, $this->review_type );
	}

	/**
	 * Gets a review image.
	 *
	 * @return string The review image.
	 */
	public function get_review_image() {
		$image_url = Fields::get_instance()->get( Fields::IMAGE );

		/**
		 * Filters a review image.
		 *
		 * @param string The review image.
		 * @param string The review type.
		 */
		return apply_filters( Review::FILTER_PREFIX . 'review_image', $image_url, $this->review_type );
	}

	/**
	 * Gets a review type.
	 *
	 * @return string The review type.
	 */
	public function get_review_type() {

		$review_type = Fields::get_instance()->get( Fields::REVIEW_TYPE );

		/**
		 * Filters a review type.
		 *
		 * @param string The review type.
		 */
		return apply_filters( Review::FILTER_PREFIX . 'review_type', $review_type );
	}

	/**
	 * Returns a sanitized array of the authors of a review.
	 *
	 * @return array The array of author data.
	 */
	public function get_author() {
		$coauthors = get_coauthors( get_the_ID() ) ?: [];

		if ( empty( $coauthors ) ) {
			return;
		}

		$coauthor      = $coauthors[0];
		$coauthor_name = ! empty( $coauthor->display_name ) ? $coauthor->display_name : '';
		$author_url    = ! empty( $coauthor->_pmc_user_google_plus ) ? $coauthor->_pmc_user_google_plus : '';

		if ( empty( $author_url ) ) {
			$author_url = get_author_posts_url( $coauthor->ID, $coauthor->user_nicename ) ?: '';
		}

		$author = array(
			'@type'  => 'Person',
			'name'   => esc_html( $coauthor_name ),
			'sameAs' => esc_url( $author_url ),
		);

		/**
		 * Filters a review's authors.
		 *
		 * @param array The review author data.
		 * @param string The review type.
		 */
		return apply_filters( Review::FILTER_PREFIX . 'author', $author, $this->review_type );
	}

	/**
	 * Filters ields specific to movies.
	 *
	 * @param array $review_data The review data.
	 * @return array The modified review data.
	 */
	public function filter_movie_fields( $review_data ) {
		$review_data['itemReviewed']['@type']         = 'Movie';
		$review_data['itemReviewed']['sameAs']        = Fields::get_instance()->get( Fields::CANONICAL_LINK );
		$review_data['itemReviewed']['image']         = $this->get_review_image();
		$review_data['itemReviewed']['datePublished'] = date( 'Y-m-d', intval( Fields::get_instance()->get( Fields::RELEASE_DATE ) ) );
		$review_data['itemReviewed']['director']      = array(
			'@type' => 'Person',
			'name'  => $this->get_review_artist(),
		);

		return $review_data;
	}

	/**
	 * Filters ields specific to music albums.
	 *
	 * @param array $review_data The review data.
	 * @return array The modified review data.
	 */
	public function filter_music_album_fields( $review_data ) {
		$review_data['itemReviewed']['@type']         = 'MusicAlbum';
		$review_data['itemReviewed']['byArtist']      = $this->get_review_artist();
		$review_data['itemReviewed']['datePublished'] = date( 'Y-m-d', intval( Fields::get_instance()->get( Fields::RELEASE_DATE ) ) );

		return $review_data;
	}
}
