<?php
/**
 * @since 2015-06-01 Hau Vong
 */

if ( !class_exists('PMC_WP_CLI') ) {
	pmc_load_plugin('pmc-wp-cli','pmc-plugins');
}

WP_CLI::add_command( 'pmc-term-split', 'PMC_Term_Split_Cli' );

class PMC_Term_Split_Cli extends PMC_WP_CLI {
	public $dry_run = false;

	protected function _extract_common_args( $assoc_args ) {
		parent::_extract_common_args( $assoc_args );
		$this->dry_run = isset( $assoc_args['dry-run'] );
	}

	/**
	 * @synopsis   [--dry-run] [--batch-size=<number>] [--sleep=<second>] [--max-iteration=<number>] [--log-file=<file>]
	 * @subcommand fix
	 */
	public function fix( $args = array(), $assoc_args = array() ) {
		global $wpdb;

		WP_CLI::line( 'Starting...' );

		$this->_extract_common_args( $assoc_args );

		add_action( 'split_shared_term', function($term_id, $new_term_id, $term_taxonomy_id, $taxonomy) {
			printf("Term split:  %s %d->%d\n", $taxonomy, $term_id, $new_term_id );
			do_action( 'pmc_cli_split_shared_term', $term_id, $new_term_id, $term_taxonomy_id, $taxonomy );
		}, 10, 4 );

		// need to use direct query to determin what taxonomy to trigger update for term splitting
		$sql = $wpdb->prepare("SELECT tt.term_id,tt.taxonomy,t.count
			FROM {$wpdb->term_taxonomy} tt
			JOIN (
				SELECT term_id,count(*) as count
				FROM {$wpdb->term_taxonomy}
				GROUP BY term_id
				HAVING count(*) > 1
			) as t
			on tt.term_id = t.term_id
			LIMIT 0,%d", $this->batch_size );

		$first_item = '';

		do {
			$results = $wpdb->get_results( $sql );
			if ( empty( $results ) ) {
				break;
			}

			$item = reset( $results );

			// detect infinite loop
			if ( $first_item == $item->taxonomy . $item->term_id ) {
				$this->_write_log("Infinite loop detected.");
				break;
			}
			$first_item = $item->taxonomy . $item->term_id;

			foreach ( $results as $item ) {
				$this->_write_log( sprintf("%s %d %d", $item->taxonomy,$item->term_id,$item->count ) );
				if ( ! $this->dry_run ) {
					// update term to trigger shared term splitting
					wp_update_term( $item->term_id, $item->taxonomy, array() );
				}
				$this->_update_iteration();
			}

		} while ( count( $results ) == $this->batch_size && ! $this->dry_run );

		$this->fix_sailthru();
		WP_CLI::line( 'Done.' );

	} // function fix

	// sailthru options
	private function fix_sailthru() {
		$sailthru_option = 'pmc_post_tag_custom_field_sailthru';
		if ( $terms = pmc_get_option( $sailthru_option ) ) {
			$new_terms = array();
			$pmc_term_split = PMC_Term_Split::get_instance();
			$changed = false;
			foreach ( $terms as $term_id => $item ) {
				if ( $new_term_id = $pmc_term_split->get_term_id( $term_id, 'post_tag' ) ) {
					$term_id = $new_term_id;
					$changed = true;
					$this->_write_log( sprintf("Sailthru: post_tag %d -> %d", $term_id, $new_term_id ) );
				}
				$new_terms[$term_id] = $item;
			}
			if ( $changed ) {
				pmc_update_option( $sailthru_option, $new_terms );
			}
		}
	}
} // class

// EOF
