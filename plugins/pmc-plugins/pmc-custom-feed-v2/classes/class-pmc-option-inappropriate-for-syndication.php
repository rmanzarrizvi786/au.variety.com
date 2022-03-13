<?php
/**
 * This class adds functionality to admin post option inappropriate for syndication.
 */

namespace PMC\Custom_Feed;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Option_Inappropriate_For_Syndication {

	use Singleton;

	protected function __construct() {
		add_filter( 'pre_get_posts', [ $this, 'exclude_inappropriate_for_syndication_posts' ] );
	}

	public function exclude_inappropriate_for_syndication_posts( $query ) {

		if ( is_feed() && 'sailthru' !== \PMC::filter_input( INPUT_GET, 'feed' ) ) {

			$existing_tax_query = $query->get( 'tax_query' );

			$new_tax_query = [
				'taxonomy' => '_post-options',
				'field'    => 'slug',
				'terms'    => 'inappropriate-for-syndication',
				'operator' => 'NOT IN',
			];

			$tax_query = [];

			// Manage existing tax_query condition.
			if ( ! empty( $existing_tax_query ) ) {

				$tax_query = [
					'relation' => 'AND',
					$new_tax_query,
				];

				$tax_query[] = $existing_tax_query;

			} else {

				$tax_query[] = $new_tax_query;
			}

			// Set term query.
			$query->set( 'tax_query', $tax_query );
		}

		return $query;

	}

	/**
	 * @param mixed $post The post object or ID to check for exclusion
	 * @return bool True if post is flagged for exclusion
	 */
	public function is_exclude( $post ) {

		$post = get_post( $post );

		if ( ! empty( $post ) ) {
			return \PMC\Post_Options\API::get_instance()->post( $post )->has_option( 'inappropriate-for-syndication' );
		}

		return false;
	}

}

PMC_Option_Inappropriate_For_Syndication::get_instance();
