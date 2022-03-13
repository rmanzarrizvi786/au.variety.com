<?php
/**
 * @ticket PPT-4753
 * @see PMC_Primary_Taxonomy
 * This class is written to use action/filters hook to fix the term split for pmc-primary-taxonomy plugin
 * We need to isolate the code so we can turn on/off without touch the plugin source
 *
 * @since 2015-06-02 Hau Vong
 */

class PMC_Term_Split_Primary_Taxonomy {
	protected function _init() {
		add_filter( 'pmc_primary_taxonomy_meta_key', array( $this, 'filter_pmc_primary_taxonomy_meta_key'), 10, 3 );
		add_action( 'pmc_cli_split_shared_term', array( $this, 'action_pmc_cli_split_shared_term' ), 10, 4 );
	}

	// need to fix the post meta
	public function filter_pmc_primary_taxonomy_meta_key( $term_id, $post_id, $taxonomy ) {
		if ( $new_term_id = PMC_Term_Split::get_instance()->get_term_id( $term_id, $taxonomy ) ) {
			if ( $new_term_id !== $term_id ) {
				update_post_meta( $post_id, PMC_Primary_Taxonomy::get_instance()->meta_key( $taxonomy ), $new_term_id, $term_id );
				$term_id = $new_term_id;
			}
		}
		return $term_id;
	}

	/**
	 * This action only fire during cli script term update
	 * This was add to avoid large data looping from being process on live site
	 * @param int $old_term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created for the $term_taxonomy_id.
	 * @param int $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy Taxonomy for the split term.
	 *
	 * @since 2015-06-02 Hau Vong
	 */
	public function action_pmc_cli_split_shared_term( $old_term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {
		// IMPORTANT: We only run this action if we're in CLI mode
		if ( ! defined('WP_CLI') || ! WP_CLI ) {
			return;
		}

		$meta_key = PMC_Primary_Taxonomy::get_instance()->meta_key( $taxonomy );
		$args = array(
			'fields'         => 'ids',
			'meta_key'       => $meta_key,
			'meta_value'     => $old_term_id,
			'posts_per_page' => 100,
			'no_found_rows'  => true,
		) ;

		$first_item = false;
		do {

			$post_ids = get_posts( $args );
			if ( ! empty( $post_ids ) ) {
				$post_id = reset( $post_ids );
				if ( $first_item && $first_item == $post_id ) {
					// Endless loop detected, let's bail out
					break;
				}
				$first_item = $post_id;
				foreach ( $post_ids as $post_id ) {
					update_post_meta( $post_id, $meta_key, $new_term_id, $old_term_id );
				}
			}

			// sleep for 1 second to give server a chance to breathe
			printf("%d posts updated, sleep for 1 second.\n", count( $post_ids ) );
			sleep(1);

		} while ( !empty( $post_ids ) );

	}

}

// EOF