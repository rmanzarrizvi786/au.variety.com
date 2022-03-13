<?php

use PMC\Global_Functions\Traits\Singleton;

class PMC_Voices {

	use Singleton;

	const _cache_grp = 'pmc_voices_cache';

	protected function __construct() {

		add_action( 'custom_metadata_manager_init_metadata', array( $this, 'init_custom_fields' ) );

	}

	/**
	 * Add Voices field for variety.
	 */
	public function init_custom_fields() {

		if ( function_exists( 'x_add_metadata_field' ) && function_exists( 'x_add_metadata_group' ) ) {

			$grp_args = array(
				'label'   => 'Teams',
				'context' => 'side',
			);

			x_add_metadata_group( '_pmc_guest_authors', PMC_Guest_Authors::post_type, $grp_args );

			$args = array(
				'group'       => '_pmc_guest_authors',
				'field_type'  => 'checkbox',
				'label'       => 'Voices',
				'description' => 'Checkbox for voices'
			);

			x_add_metadata_field( '_pmc_voices', PMC_Guest_Authors::post_type, $args );

		}
	}

	/**
	 * Get Guest authors who has voices checked.
	 *
	 * @param string $order_by
	 *
	 * @return array|bool|mixed
	 */
	public function get_guest_author_voices( $order_by = "" ) {

		$key = 'pmc_voices_guest_authors'.$order_by;

		$guest_authors = wp_cache_get( $key, PMC_Voices::_cache_grp );

		if ( !empty( $guest_authors ) ) {
			return $guest_authors;
		}

		$args = array(
			'meta_query'  => array(
				array(
					'key'   => '_pmc_voices',
					'value' => 'on',
				)
			),
			'post_type'   => PMC_Guest_Authors::post_type,
			'numberposts' => 50 );

		if ( ! empty( $order_by ) ) {
			$args['orderby'] = $order_by;
		}

		$guest_authors = get_posts( $args );

		if ( !empty( $guest_authors ) )
			wp_cache_set( $key, $guest_authors, PMC_Voices::_cache_grp, 300 );

		return $guest_authors;
	}

	/**
	 * Last post by author of post type post
	 *
	 * @param $author_id
	 * @param $author_login
	 *
	 * @return array
	 */
	function guest_author_last_post( $author_id, $author_login ) {

		$req_post_info = wp_cache_get( '_last_author_post_' . $author_id, PMC_Voices::_cache_grp );

		if ( isset( $req_post_info ) && !empty( $req_post_info ) ) {

			return (array)$req_post_info;

		} else {
			$author_post_array = array();

			$args = array(
				'tax_query'      => array(
					array(
						'taxonomy' => 'author',
						'field'    => 'slug',
						'terms'    => 'cap-' . $author_login
					)
				),
				'posts_per_page' => '1',
			);

			$last_post = get_posts( $args );

			if ( !empty( $last_post ) && isset( $last_post[0] ) ) :

				$last_post = $last_post[0];

				$author_post_array['title'] = pmc_get_title( $last_post->ID );

				$author_post_array['url'] = get_permalink( $last_post->ID );

				if ( is_array( $author_post_array ) ) {

					wp_cache_set( '_last_author_post_' . $author_id, $author_post_array, PMC_Voices::_cache_grp, 300 );
				}

			endif;

			return (array)$author_post_array;
		}

	}

	function get_guest_author_by_id( $id ) {

		$co_author_guest = new CoAuthors_Guest_Authors;

		if ( $id > 0 )
			return $co_author_guest->get_guest_author_by( 'id', $id );
	}

}
//EOF
