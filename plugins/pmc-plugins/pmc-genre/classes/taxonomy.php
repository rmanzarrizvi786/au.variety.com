<?php
/**
 * The Genre taxonomy class which registers the taxonomy
 *
 * @author Amit Gupta <agupta@pmc.com>
 */

namespace PMC\Genre;


class Taxonomy extends Base {

	/**
	 * @var array An array of post types on which Genre taxonomy is enabled
	 */
	protected $_post_types = array(
		'post',
		'pmc-gallery',
		'pmc_top_video',
	);

	/**
	 * @var array Default list of genres which must be available on all sites.
	 */
	protected $_default_terms = array(
		'Supernatural/Sci-Fi',
		'Comedy',
		'Drama',
		'Romantic Comedy',
		'Dramedy',
		'Thriller',
		'Action',
		'Limited Release/Arthouse/Indie',
		'Horror',
		'Superhero/Comic',
		'Reality TV',
	);

	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup listeners on action and filter hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() {
		/**
		 * Actions
		 */
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'admin_init', array( $this, 'add_default_terms' ), 11 );

	}

	/**
	 * This function registers the Genre taxonomy. Its not meant to be called directly.
	 *
	 * @return void
	 */
	public function register_taxonomy() {
		register_taxonomy( self::NAME, $this->get_post_types(), array(
			'label'         => 'Genre',
			'labels' => array(
				'name'               => __( 'Genres', 'pmc-plugins' ),
				'singular_name'      => __( 'Genre', 'pmc-plugins' ),
				'add_new_item'       => __( 'Add New Genre', 'pmc-plugins' ),
				'edit_item'          => __( 'Edit Genre', 'pmc-plugins' ),
				'new_item'           => __( 'New Genre', 'pmc-plugins' ),
				'view_item'          => __( 'View Genre', 'pmc-plugins' ),
				'search_items'       => __( 'Search Genres', 'pmc-plugins' ),
				'not_found'          => __( 'No Genres found.', 'pmc-plugins' ),
				'not_found_in_trash' => __( 'No Genres found in Trash.', 'pmc-plugins' ),
				'all_items'          => __( 'Genres', 'pmc-plugins' ),
			),
			'public'        => true,
			'show_ui'       => true,
			'hierarchical'  => true,
			// admins only
			'capabilities'  => array(
				'manage_terms'  => $this->_capability, // admin+
				'edit_terms'    => $this->_capability, // admin+
				'delete_terms'  => $this->_capability, // admin+
				'assign_terms'  => 'edit_posts', // contributor+
			),
		) );
	}

	/**
	 * This function retuns the post types on which the Genre taxonomy
	 * should be enabled.
	 *
	 * @return array
	 */
	public function get_post_types() {
		$post_types = apply_filters( 'pmc-genre-post-types', $this->_post_types );

		if ( ! is_array( $post_types ) ) {
			$post_types = $this->_post_types;
		}

		return array_filter( array_unique( array_values( $post_types ) ) );
	}

	/**
	 * Return all genre terms for a post
	 */
	public function get_post_terms( $post ) {
		$terms = get_the_terms( $post, self::NAME );

		if ( is_array( $terms ) && ! is_wp_error( $terms ) ) {
			return $terms;
		}

		return false;
	}

	/**
	 * Return all genre terms.
	 */
	public function get_terms() {
		return Helper::get_terms( self::NAME );
	}

	/**
	 * Return all genre terms in an array with term_id as
	 * key and term name as value
	 */
	public function get_terms_array() {
		return Helper::get_terms_array( self::NAME );
	}

	/**
	 * Called on 'admin_init' action, this function makes sure default genres are
	 * present in the DB. This function is not meant to be called directly.
	 *
	 * @return void
	 */
	public function add_default_terms() {
		/*
		 * Add default terms only if current user is an admin
		 */
		if ( ! current_user_can( $this->_capability ) ) {
			return;
		}

		if ( empty( $this->_default_terms ) || ! is_array( $this->_default_terms ) ) {
			return;
		}

		$this->maybe_add_terms( array_values( $this->_default_terms ) );
	}

	/**
	 * This function accepts an array of terms and adds them if they do not
	 * exist in DB already.
	 *
	 * @param array $terms An array of terms which must be added
	 * @return void
	 */
	public function maybe_add_terms( array $terms = array() ) {
		$terms = array_filter( array_unique( $terms ) );

		if ( empty( $terms ) ) {
			return;
		}

		/**
		 * Let's fetch the existing terms for our taxonomy so that we
		 * add only the non-existing terms.
		 */
		$existing_terms = get_terms( self::NAME, array(
			'hide_empty' => false,
			'fields'     => 'names',
		) );

		if ( ! is_array( $existing_terms ) || empty( $existing_terms ) || is_wp_error( $existing_terms ) ) {
			$existing_terms = array();
		} else {
			$existing_terms = array_values( $existing_terms );
		}

		/*
		 * Weed out the terms which already exist so that we would be
		 * left only with those terms which need to be added.
		 */
		$terms_to_add = array_diff( $terms, $existing_terms );

		/*
		 * Loop over the array only if there are any
		 * terms which need to be added
		 */
		if ( ! empty( $terms_to_add ) ) {
			foreach ( $terms_to_add as $term ) {
				//add the term
				wp_insert_term( $term, self::NAME );
			}
		}

		unset( $terms_to_add, $existing_terms );
	}

}	// end class


//EOF
