<?php

namespace PMC\Zoninator_Extended;

use PMC\Global_Functions\Traits\Singleton;


/**
 * Class Edit_Posts
 *
 * @package pmc-zoninator-extended
 */
class Edit_Posts {

	use Singleton;

	/**
	 * Capability to edit post in zone page.
	 *
	 * @var string
	 */
	protected static $capability = 'edit_posts';

	/**
	 * Function to initialize post edit apis.
	 */
	protected function __construct() {
		add_action( 'wp_ajax_pmc_zoninator_get_post', array( $this, 'get_post' ) );
		add_action( 'wp_ajax_pmc_zoninator_update_post', array( $this, 'update_post' ) );
	}

	/**
	 * Ajax callback, to get post data.
	 *
	 * @return void
	 */
	public function get_post() {
		$post_id = ( ! empty( $_POST['post_id'] ) ) ? (int) $_POST['post_id'] : 0;
		$zone_id = ( ! empty( $_POST['zone_id'] ) ) ? (int) $_POST['zone_id'] : 0;

		if ( empty( $post_id ) ||
			empty( $zone_id ) ||
			empty( $_POST['nonce'] ) ||
			( ! wp_verify_nonce( $_POST['nonce'], 'pmc_zoninator_get_post-' . $zone_id ) ) ||
			( ! current_user_can( self::$capability, $post_id ) )
		) {
			wp_send_json_error();
		}
		check_ajax_referer( 'pmc_zoninator_manage_post', 'security' );

		$post = get_post( $post_id, ARRAY_A );

		if ( empty( $post ) || is_wp_error( $post ) ) {
			wp_send_json_error();
		}

		$post_type  = ( ! empty( $post['post_type'] ) ) ? sanitize_title( $post['post_type'] ) : '';
		$taxonomies = get_object_taxonomies( $post_type, ARRAY_A );

		if ( ! empty( $taxonomies['editorial'] ) && is_a( $taxonomies['editorial'], 'WP_Taxonomy' ) ) {

			$editorials             = get_the_terms( $post_id, 'editorial' );
			$editorials             = ( ! empty( $editorials ) && ! is_wp_error( $editorials ) ) ? $editorials : array();
			$editorials             = wp_list_pluck( $editorials, 'term_id' );
			$editorials             = array_map( 'intval', $editorials );
			$post['post_editorial'] = $editorials;

		}

		wp_send_json_success( $post );
	}

	/**
	 * Ajax callback, to update post taxonomy data.
	 *
	 * @return void
	 */
	public function update_post() {
		$post_id = ( ! empty( $_POST['post_id'] ) ) ? (int) $_POST['post_id'] : 0;
		$zone_id = ( ! empty( $_POST['zone_id'] ) ) ? (int) $_POST['zone_id'] : 0;

		if ( empty( $post_id ) ||
			empty( $zone_id ) ||
			empty( $_POST['nonce'] ) ||
			( ! wp_verify_nonce( $_POST['nonce'], 'pmc_zoninator_update_post-' . $zone_id ) ) ||
			( ! current_user_can( self::$capability, $post_id ) )
		) {
			wp_send_json_error();
		}
		check_ajax_referer( 'pmc_zoninator_manage_post', 'security' );

		$post = get_post( $post_id, ARRAY_A );

		if ( empty( $post ) || is_wp_error( $post ) ) {
			wp_send_json_error();
		}

		$post_type  = ( ! empty( $post['post_type'] ) ) ? sanitize_title( $post['post_type'] ) : '';
		$taxonomies = get_object_taxonomies( $post_type, ARRAY_A );

		if ( ! empty( $taxonomies['category'] ) && is_a( $taxonomies['category'], 'WP_Taxonomy' ) ) {

			$categories = ( ! empty( $_POST['data']['category'] ) && is_array( $_POST['data']['category'] ) ) ? array_map( 'intval', wp_unslash( $_POST['data']['category'] ) ) : array();
			$categories = array_unique( $categories );

			$term_taxonomy_ids = wp_set_object_terms( $post_id, $categories, 'category' );

			if ( is_wp_error( $term_taxonomy_ids ) ) {
				wp_send_json_error();
			}
		}

		if ( ! empty( $taxonomies['editorial'] ) && is_a( $taxonomies['editorial'], 'WP_Taxonomy' ) ) {

			$editorial = ( ! empty( $_POST['data']['editorial'] ) && is_array( $_POST['data']['editorial'] ) ) ? array_map( 'intval', wp_unslash( $_POST['data']['editorial'] ) ) : array();
			$editorial = array_unique( $editorial );

			$term_taxonomy_ids = wp_set_object_terms( $post_id, $editorial, 'editorial' );

			if ( is_wp_error( $term_taxonomy_ids ) ) {
				wp_send_json_error();
			}
		}

		wp_send_json_success();

	}

}

