<?php

/*
 * Delete all sitemaps so they can be regenerated
 * @version 2015-08-26 Harshad Pandit - PPT-5239 - added dry run to delete_all command, fixed warnings and notices and logging
 */
WP_CLI::add_command('delete-sitemap', 'PMC_WP_CLI_Delete_Sitemap');

class PMC_WP_CLI_Delete_Sitemap extends PMC_WP_CLI_Base {

	public function __construct(  $args = array(), $assoc_args = array() ) {
		parent::__construct( $args, $assoc_args );
	}

	public function delete_all(  $args = array(), $assoc_args = array() ) {
		$total_count = 0;
		$this->_extract_common_args( $assoc_args );
		do {
			$wpquery = new WP_Query( array (
				'fields'           => 'ids',
				'post_type'        => 'pmc_sitemap',
				'cache_results'    => false,
				'suppress_filters' => true,
				'offset'           => 0,
				'posts_per_page'   => $this->batch_size,
				'post_status'      => 'any',
			) );

			if ( $wpquery->post_count == 0 ) {
				$this->_write_log( "No sitemaps found" );
			}

			if ( $wpquery->post_count ) {

				if ( $this->dry_run ) {
					WP_CLI::line( "Sitemaps found to delete {$wpquery->post_count}" );
					break;
				}

				foreach( $wpquery->posts as $post_id ) {

					$total_count = $total_count + 1;
					WP_CLI::line( "Sitemaps found: {$wpquery->post_count}, deleting sitemap number {$total_count}" );

					$this->_write_log( "Deleting sitemap post_id: " . $post_id);
					wp_delete_post( $post_id, true );
					$this->_update_interation();
				} // foreach
			} // if
		} while ( $wpquery->post_count > 0);	// we still have something to do
	}

}

// EOF
