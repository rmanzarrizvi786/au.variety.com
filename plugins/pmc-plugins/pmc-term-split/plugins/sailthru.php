<?php
/**
 * @ticket PPT-4736
 * @see pmc_add_tag_custom_field & pmc_save_tag_custom_field
 * This class is written to use action/filters hook to fix the term split for sailthru
 * We need to isolate the code so we can turn on/off without touch the plugin source
 *
 * @since 2015-06-02 Hau Vong
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Term_Split_Sailthru {

	use Singleton;

	protected function __construct() {
		add_action( 'split_shared_term', array( $this, 'action_split_shared_term' ), 10, 4 );
	}

	/**
	 * Check pmc_get_option stored as associated array in 'pmc_post_tag_custom_field_sailthru'
	 *
	 * @param int $old_term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created for the $term_taxonomy_id.
	 * @param int $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy Taxonomy for the split term.
	 */
	public function action_split_shared_term( $old_term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {
		// only process on post tag taxonomy
		if ( 'post_tag' !== $taxonomy ) {
			return;
		}

		$sailthru_option = 'pmc_post_tag_custom_field_sailthru';
		if ( $terms = pmc_get_option( $sailthru_option ) ) {
			// do we have existing term mapping & not already re-mapped?
			if ( isset( $terms[ $old_term_id ] ) && ! isset( $terms[ $new_term_id ] ) ) {
				// move from old to new
				$terms[ $new_term_id ] = $terms[ $old_term_id ];
				unset( $terms[ $old_term_id ] );
				pmc_update_option( $sailthru_option, $terms );
			}
		}

	}

}

PMC_Term_Split_Sailthru::get_instance();

// EOF
