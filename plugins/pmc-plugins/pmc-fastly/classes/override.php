<?php
namespace PMC\Fastly;

use CheezCapDropdownOption;
use PMC_Cheezcap;
use PMC\Global_Functions\Traits\Singleton;
use Purgely;

class Override {
	use Singleton;

	const TERM_KEYS_NAME = 'pmc_fastly_term_keys';

	/**
	 * Note: We're keep all filter & actions added within the constructor to prevent code change outside of Singleton object
	 * This also allow us to enforce and mandate testing the construct function in unit test and do code coverage
	 * to test plugin activation.
	 */
	protected function __construct() {

		add_filter( 'pmc_global_cheezcap_options', [ $this, 'filter_pmc_global_cheezcap_options' ] );

		if ( is_admin() ) {
			// IMPORTANT: We need to always trigger pre-build terms so that the post meta always sync up with the post taxonomy
			// We're using priority 9999 here to discourage other plugin from override our filter.
			// Normally we would not use any priority over 100 for any purpose.
			// Fastly need to initialize early with high priority, it also need to capture all filters and changes
			// by run the filter last with very low priority.
			add_action( 'save_post', [ $this, 'build_term_keys' ], 9999 );
		}

		if ( 'yes' !== PMC_Cheezcap::get_instance()->get_option( 'pmc_fastly_surrogate_keys_prebuild' ) ) {
			return;
		}

		add_filter( 'purgely_pre_term_keys', [ $this, 'filter_purgely_pre_term_keys' ], 10, 2 );
		Purgely::instance();

	}

	public function filter_pmc_global_cheezcap_options( $cheezcap_options ) {

		$cheezcap_options[] = new CheezCapDropdownOption(
			'Prebuild Fastly Surrogate Keys',
			'Override the fastly surrogate keys with pre-build keys',
			'pmc_fastly_surrogate_keys_prebuild',
			[ 'no', 'yes' ],
			0, // 1sts option => no
			[ 'No', 'Yes' ]
		);

		return $cheezcap_options;

	}

	/**
	 * @param array $keys
	 * @param \WP_Query $wp_query
	 * @return mixed
	 *
	 * @see Purgely_Surrogate_Key_Collection::__construct
	 *
	 */
	public function filter_purgely_pre_term_keys( $keys, $wp_query ) {

		if ( is_object( $wp_query ) && is_a( $wp_query, \WP_Query::class ) && $wp_query->is_single() ) {
			$term_keys = get_post_meta( $wp_query->post->ID, self::TERM_KEYS_NAME, true );
			if ( empty( $term_keys ) || ! is_array( $term_keys ) ) {
				// Note: Do not cast the result value here, we need it to determine if it is an invalid entry or not.
				$term_keys = $this->build_term_keys( $wp_query->post->ID );
			}
			if ( is_array( $term_keys ) ) {
				$keys = $term_keys;
			}
		}

		return $keys;

	}

	/**
	 * Function to pre build the post's term keys for fastly surrogate cache keys
	 * and stored in post's meta for faster retrieval when the article are being render.
	 * This give us a single operation vs multiple calls to get_the_terms on demand when caches are not primed
	 *
	 * @param $post_ID
	 * @return mixed
	 */
	public function build_term_keys( $post_id ) {

		$post = get_post( $post_id );
		if ( empty( $post ) ) {
			return false;
		}
		$post_type_object = get_post_type_object( get_post_type( $post ) );

		if ( empty( $post_type_object ) || ! $post_type_object->public ) {
			return false; // boolean to indicate invalid entries
		}

		$term_keys  = [];
		$taxonomies = apply_filters( 'purgely_taxonomy_keys', (array) get_taxonomies() );

		if ( is_array( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$terms = get_the_terms( $post_id, $taxonomy );
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						if ( isset( $term->term_id ) ) {
							$term_keys[] = 't-' . $term->term_id;
						}
					}
				}
			}
		}

		$author = absint( $post->post_author );
		if ( $author > 0 ) {
			$term_keys[] = 'a-' . $author;
		}

		// @TODO: We probably consider integrate with pmc guest author too for future improvement?

		if ( ! empty( $term_keys ) ) {
			update_post_meta( $post_id, self::TERM_KEYS_NAME, $term_keys );
		}

		return (array) $term_keys;
	}

}
