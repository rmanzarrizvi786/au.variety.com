<?php
/**
 * @ticket PPT-4870
 * @see PMC_SEO_Tweaks_Taxonomy
 * This class is written to use action/filters hook to fix the term split for pmc-seo-tweaks plugin
 * We need to isolate the code so we can turn on/off without touch the plugin source
 *
 * @since 2015-06-02 Hau Vong
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Term_Split_Seo_Tweaks {

	use Singleton;

	protected function __construct() {
		add_action( 'split_shared_term', array( $this, 'action_split_shared_term' ), 10, 4 );
	}

	/**
	 * @ticket PPT-4870 - WP 4.2 Split Taxonomy Terms
	 * @since 2015-05-19 Archana Mandhare
	 * @modified 2015-06-02 Hau Vong
	 *
	 * Check pmc_get_option with old term id when a term gets split to see if any of them
	 * need to be updated with the new term id.
	 *
	 * @param int $old_term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created for the $term_taxonomy_id.
	 * @param int $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy Taxonomy for the split term.
	 */
	public function action_split_shared_term( $old_term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

		if ( ! class_exists('PMC_SEO_Tweaks_Taxonomy') ) {
			return;
		}

		// get the supported whitelist taxonomies
		// @modified 2015-06-02
		$taxomomies = PMC_SEO_Tweaks_Taxonomy::get_instance()->get_taxonomies();
		// Only process term split if plugin support the taxonomies
		if ( ! in_array( $taxonomy, $taxomomies ) ) {
			return;
		}

		// First check if we already have the new term option key
		$taxonomy_term = pmc_get_option( PMC_SEO_Tweaks_Taxonomy::option_name . $new_term_id );

		if( empty( $taxonomy_term ) ) {
			// migrate data from old term to new term
			$taxonomy_term = pmc_get_option( PMC_SEO_Tweaks_Taxonomy::option_name . $old_term_id );
			// If we found SEO term data in old option, create new option key with new term ID and migrate the data.
			if ( $taxonomy_term ) {
				pmc_update_option( PMC_SEO_Tweaks_Taxonomy::option_name . $new_term_id , $taxonomy_term );
			}
		}

	} // function action_split_shared_term
}

PMC_Term_Split_Seo_Tweaks::get_instance();

// EOF
