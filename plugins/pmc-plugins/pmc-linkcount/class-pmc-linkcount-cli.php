<?php
/**
 * PMC_LinkCount CLI script : Save count of links inside post_content in post meta
 * @since 2015-08-17
 * @version 2015-08-17 Archana Mandhare PPT-5297
 */

class PMC_LinkCount_Cli extends PMC_WP_CLI {

	public $dry_run = false;

	protected function _extract_common_args( $assoc_args ) {
		parent::_extract_common_args( $assoc_args );
		$this->dry_run = isset( $assoc_args['dry-run'] );
	}

	/**
	 * Gets the posts for the year passed in $args (default is current year) and scans post_content
	 * to save the count of links(external, internal article and internal tags) in post meta
	 *
	 * @subcommand startcount
	 * @synopsis   [--dry-run] [--year=<year>] [--batch-size=<number>] [--sleep=<second>] [--max-iteration=<number>] [--log-file=<file>]
	 * Example usage : wp --url=bgr.com linkcount startcount --dry-run=1 --year=2015
	 */
	public function startcount( $args = array(), $assoc_args = array() ) {

		WP_CLI::line( 'Starting...' );

		$this->_extract_common_args( $assoc_args );

		$total_count      = 0;
		$page             = 1;
		$year             = ! empty( $assoc_args['year'] ) ? $assoc_args['year'] : date( 'Y' );
		$post_types       = apply_filters( \PMC_LinkCount::FILTER_ALLOW_POST_TYPE, PMC_LinkCount::get_instance()->post_types );
		$post_types       = apply_filters( 'pmc_post_linkcount_post_type_whitelist', $post_types ); // @TODO: SADE-517 to be removed
		$print_post_types = json_encode( $post_types );

		do {
			$wpquery = new WP_Query( array(
				'post_type'        => $post_types,
				'cache_results'    => false,
				'suppress_filters' => true,
				'posts_per_page'   => $this->batch_size,
				'paged'            => $page,
				'post_status'      => 'publish',
				'year'             => $year,
			) );

			if ( $wpquery->post_count ) {

				WP_CLI::line( "---------------------------------------------------------------" );

				WP_CLI::line( "Updating posts for year : {$year} and page {$page} and post_types are {$print_post_types}" );

				WP_CLI::line( "---------------------------------------------------------------" );

				WP_CLI::line( "Posts found {$wpquery->post_count} : Total Updated = {$total_count}" );

				WP_CLI::line( "---------------------------------------------------------------" );

				$total_count += $wpquery->post_count;
				$page = $page + 1;
				foreach ( $wpquery->posts as $postObj ) {

					WP_CLI::line( "Linkcount updating post ID = {$postObj->ID} and post_type = {$postObj->post_type}" );

					if ( ! $this->dry_run ) {

						$this->_save_linkcount( $postObj );
					}

					$this->_update_interation();
				} // foreach

			} // if

		} while ( $wpquery->post_count > 0 );    // we still have something to do

		WP_CLI::line( "*****  Done updating {$total_count} posts  ****" );

	} // function startcount


	private function _save_linkcount( $post ) {
		PMC_LinkCount::get_instance()->save_linkcount_in_post_meta( $post->ID, $post->post_content );
	}

} // class

WP_CLI::add_command( 'linkcount', 'PMC_LinkCount_Cli' );

// EOF
