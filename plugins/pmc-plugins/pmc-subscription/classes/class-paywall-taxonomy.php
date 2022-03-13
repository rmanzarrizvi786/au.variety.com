<?php
namespace PMC\Subscription;

use \PMC\Global_Functions\Traits\Singleton;

class Paywall_Taxonomy {

	use Singleton;

	var $taxonomy = 'pmc-subscription';

	/**
	 * Class instantiation
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		add_action( 'init', [ $this, 'register_taxonomy' ] );
	}

	/**
	 * Register PMC Subscription plugin taxonomy.
	 * `pmc-subscription` taxonomy by default creates terms for `not-behind-paywall` and `behind-paywall`.
	 * Themes and plugins can extend to create other terms needed. These terms should
	 * always be set programmatically.
	 *
	 * @return void
	 */
	public function register_taxonomy() : void {
		$taxonomy   = $this->taxonomy;
		$post_types = $this->get_permitted_post_types();
		$args       = [
			'public'            => true,
			'show_ui'           => false,
			'show_in_nav_menus' => false,
			'hierarchical'      => true,
		];

		register_taxonomy( $taxonomy, $post_types, $args );

		// Create default terms of `not-behind-paywall` and `behind-paywall`.
		// All subscription posts will fall in one bucket
		// or the other.
		$default_terms = [
			'not-behind-paywall' => __( 'Not Behind Paywall', 'pmc-subscription' ),
			'behind-paywall'     => __( 'Behind Paywall', 'pmc-subscription' ),
		];

		$this->add_taxonomy_term_if_not_exist( $default_terms );
	}

	/**
	 * Helper to create pmc-subscription terms if they do no exist.
	 *
	 * @param array $terms
	 * @return void
	 */
	public function add_taxonomy_term_if_not_exist( $terms ) : void {
		// Bail if there are no terms given, or if the current user cannot edit posts.
		if ( empty( $terms ) || ! is_array( $terms ) || ! current_user_can( 'edit_posts' ) ) {
			return; // @codeCoverageIgnore
		}

		// Loop through each term.
		foreach ( $terms as $term_slug => $term_name ) {
			// Check if the term exists.
			if ( function_exists( 'wpcom_vip_term_exists' ) ) {
				$term_exists = wpcom_vip_term_exists( $term_slug, $this->taxonomy );
			} else {
				// @codeCoverageIgnoreStart
				$term_exists = term_exists( $term_slug, $this->taxonomy ); // phpcs:ignore
				// @codeCoverageIgnoreEnd
			}

			// Create the term if it does not already exist
			if ( empty( $term_exists ) || ! is_array( $term_exists ) ) {
				wp_insert_term( $term_name, $this->taxonomy, [ 'slug' => $term_slug ] );
			}
		}

	}

	/**
	 * Get posts types that are registered to use this taxonomy.
	 * By default `post` is the only post type.
	 *
	 * @return array
	 */
	public function get_permitted_post_types() : array {
		return (array) apply_filters( 'pmc_subscription_taxonomy_post_types', [ 'post' ] );
	}

}
