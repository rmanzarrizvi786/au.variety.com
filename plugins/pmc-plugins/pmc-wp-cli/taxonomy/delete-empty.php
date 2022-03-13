<?php namespace PMC\WPCLI\Taxonomy;
/**
 * This class is create to implement the cli command: pmc-taxonomy delete-empty
 * @see PMC_WP_CLI_Taxonomy::delete_empty
 *
 * The class was extended from \PMC_WP_CLI_Base too allow help functions usage within the class
 * and the class code is keep within the class to prevent code clustering the main wp cli pmc-taxonomy class PMC_WP_CLI_Taxonomy
 *
 */

class DeleteEmpty extends \PMC_WP_CLI_Base {

	/**
	 * Main function to run
	 * @see PMC_WP_CLI_Taxonomy::delete_empty
	 */
	public function run() {
		global $wpdb;


		$this->_write_log( sprintf('Delete empty taxonomy start%s', $this->dry_run ? ' - Dry run' : '' ) );
		$taxonomies = explode( ',', $this->_assoc_args['taxonomy'] );

		$this->_write_log( sprintf('Taxonomy: %s', implode( ', ', $taxonomies ) ) );

		$this->_confirm_before_continue( 'Proceed?' );

		// escape the taxonomies for sql statements
		$taxonomies = array_map(
				function($v) use( $wpdb ) {
				 	return $wpdb->prepare('%s',$v);
				 },
				$taxonomies
			);

		// use direct sql query with left join to find term that have not associate to any posts
		// the taxonomy count is not reliable to determine if term is being assigned
		$sql = "select tt.term_id,tt.taxonomy,tt.count
			from {$wpdb->term_taxonomy} as tt
			left join {$wpdb->term_relationships} tr on tt.term_taxonomy_id = tr.term_taxonomy_id
			where tr.object_id is null
			and tt.taxonomy in (". implode(',', $taxonomies ) .") ";

		$offset = 0;
		do {
			// we need to process data in chunk
			$sql = $sql . $wpdb->prepare( ' LIMIT %d,%d ', $offset, $this->batch_size );
			// update offset so we don't forget, avoiding endless loop
			$offset += $this->batch_size;

			$results = $wpdb->get_results( $sql );
			foreach( $results as $item ) {

				$term = get_term( $item->term_id, $item->taxonomy );
				if ( !$term || is_wp_error( $term ) ) {
					// if there is error or term not exist, there isn't much we can do
					continue;
				}

				$this->_write_log( sprintf('%d, %d, %s, %s', $term->term_id, $term->count, $term->slug, $term->name ) );

				if ( ! $this->dry_run ) {
					wp_delete_term( $term->term_id, $term->taxonomy );
					// call helper function to clean up resource as needed, eg. stop_the_insanity
					$this->_update_iteration();
				}

			} // for

		} while( count( $results ) == $this->batch_size );

		$this->_write_log( sprintf('Done%s', $this->dry_run ? ' - Dry run' : '' ) );

	}

}