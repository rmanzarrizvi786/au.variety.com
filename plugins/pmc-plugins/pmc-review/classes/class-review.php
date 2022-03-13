<?php
/**
 * Review class.
 *
 * @package pmc-review
 * @since 2018-05-11
 */

namespace PMC\Review;

use PMC\Global_Functions\Traits\Singleton;

class Review {

	use Singleton;

	/**
	 * A plugin-specific prefix to prepend to filter names.
	 */
	const FILTER_PREFIX = 'pmc_review_';

	/**
	 * The slugs of the review categories to which this plugin's functionality should apply.
	 *
	 * @var array
	 */
	public $review_category_slugs = array( 'review' );

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Add callbacks for hooks.
	 */
	protected function _setup_hooks() {

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

	}

	/**
	 * Provides the category slugs to use in the plugin.
	 *
	 * @return array An array of category slugs.
	 */
	public function get_review_category_slugs() {
		static $filter_applied = false;

		if ( ! $filter_applied ) {
			$this->review_category_slugs = apply_filters( self::FILTER_PREFIX . 'category_slugs', $this->review_category_slugs );
			$filter_applied              = true;
		}

		return $this->review_category_slugs;
	}

	/**
	 * Provides review category data.
	 *
	 * @return array An associative array consisting of [ category_slug => category_object ].
	 */
	public function get_review_categories() {
		static $review_categories = null;

		if ( null === $review_categories || empty( array_filter( $review_categories ) ) ) {

			$review_categories = array_reduce(
				$this->get_review_category_slugs(),
				function( $review_categories, $slug ) {
					$review_categories[ $slug ] = get_category_by_slug( $slug );
					return $review_categories;

				},
				array()
			);
		}

		return $review_categories;
	}

	/**
	 * Enqueue the main admin scripts and styles.
	 */
	public function admin_enqueue_scripts() {
		global $pagenow;

		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_style( 'pmc-review-admin', PMC_REVIEW_URL . 'assets/build/pmc-review.css' );
		wp_enqueue_script( 'pmc-hooks' );
		wp_enqueue_script( 'pmc-review-admin', PMC_REVIEW_URL . 'assets/build/pmc-review.js' );
		wp_localize_script( 'pmc-review-admin', 'pmcReviewData', $this->get_localized_data() );

	}

	/**
	 * Returns data to localize for the main plugin JS.
	 *
	 * @return array An associative array of data to localize.
	 */
	public function get_localized_data() {

		$localized_data = array(
			'reviewCategories'     => $this->get_review_categories(),
			'fieldDescriptiveText' => Fields::get_instance()->get_field_descriptive_text(),
		);

		/**
		 * Filters data localized to the plugin JS.
		 *
		 * @param array An associative array of data to localize.
		 */
		return apply_filters( self::FILTER_PREFIX . 'localized_data', $localized_data );
	}

	/**
	 * Check whether the current post is a valid review post.
	 *
	 * @return bool True if the post is valid; otherwise false.
	 */
	public function is_valid_review_post( $post_id = null ) {

		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		// Required fields for JSON output.
		$is_valid = ! empty( Fields::get_instance()->get( Fields::REVIEW_TYPE, $post_id ) )
			&& ! empty( Fields::get_instance()->get( Fields::RATING, $post_id ) )
			&& ! empty( Fields::get_instance()->get( Fields::RATING_OUT_OF, $post_id ) )
			&& ! empty( Fields::get_instance()->get( Fields::SNIPPET, $post_id ) )
			&& ! empty( Fields::get_instance()->get( Fields::TITLE, $post_id ) )
			&& ! empty( Fields::get_instance()->get( Fields::ARTIST, $post_id ) );

		return apply_filters( self::FILTER_PREFIX . 'is_valid_review_post', $is_valid, $post_id );

	}

}
