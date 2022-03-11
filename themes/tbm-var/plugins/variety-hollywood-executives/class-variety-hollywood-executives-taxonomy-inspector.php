<?php

/**
 * Class Variety_Hollywood_Executives_Taxonomy_Inspector
 *
 * Display VY 500 Exec ID hidden taxonomy in post edit screen
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Hollywood_Executives_Taxonomy_Inspector {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes' ) );
	}

	public function action_add_meta_boxes() {
		add_meta_box( 'vy-hidden-taxonomy-inspector', __( 'VY Exec Profile Hidden Taxonomy-Inspector', 'pmc-variety' ), array( $this, 'display_hidden_taxonomy' ), 'hollywood_exec', 'side' );
	}

	/**
	 * Display terms in metabox.
	 */
	public function display_hidden_taxonomy() {

		global $post;

		$terms = get_the_terms( $post, Variety_Hollywood_Executives_Profile::VY_500_VARIETY_ID_TAXANOMY );

		if ( empty( $terms ) || is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return;
		}

		foreach ( $terms as $term ) {
			printf( '<p>%s</p>', esc_html( $term->slug ) );
		}

	}

}

Variety_Hollywood_Executives_Taxonomy_Inspector::get_instance();

//EOF
