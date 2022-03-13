<?php
/**
 * Additional WP-CLI commands for Safe Redirect Manager plugin
 * which are not included in the plugin itself.
 *
 * @author Amit Gupta <agupta@pmc.com>
 */


WP_CLI::add_command( 'pmc-safe-redirect-manager', 'PMC_WP_CLI_Safe_Redirect_Manager' );

class PMC_WP_CLI_Safe_Redirect_Manager extends PMC_WP_CLI_Base {

	/**
	 * Method to return Redirect Rule meta neatly in an array with typecasted values
	 *
	 * @param integer $post_id Post ID of redirect rule
	 * @return array Array containing all relevant post meta for redirect rule else empty array if no post meta found
	 */
	protected function _get_meta( $post_id ) {

		if ( empty( $post_id ) || intval( $post_id ) < 1 ) {
			return array();
		}

		$meta = get_metadata( 'post', $post_id );

		if ( empty( $meta ) ) {
			return array();
		}

		return array(
			'_redirect_rule_from'        => ( ! empty( $meta['_redirect_rule_from'][0] ) ) ? $meta['_redirect_rule_from'][0] : '',
			'_redirect_rule_to'          => ( ! empty( $meta['_redirect_rule_to'][0] ) ) ? $meta['_redirect_rule_to'][0] : '',
			'_redirect_rule_status_code' => ( ! empty( $meta['_redirect_rule_status_code'][0] ) ) ? intval( $meta['_redirect_rule_status_code'][0] ) : 0,
			'_redirect_rule_from_regex'  => ( ! empty( $meta['_redirect_rule_from_regex'][0] ) ) ? intval( $meta['_redirect_rule_from_regex'][0] ) : 0,
		);

	}

	/**
	 * Export redirect rules to a CSV
	 *
	 * @subcommand export-csv
	 *
	 * ## OPTIONS
	 *
	 * [--status-code=<status-code>]
	 * : Status code of rules which are to be exported. If not specified or set as 'all', all rules are exported.
	 *
	 * --log-file=<log-file>
	 * : Path/Filename to the log file
	 *
	 * --csv-file=<csv-file>
	 * : Path/Filename to the CSV file
	 *
	 * ## EXAMPLES
	 *
	 *		wp pmc-safe-redirect-manager export-csv --csv-file=/var/log/pmc-safe-redirect-manager-rules.csv --status-code=301 --log-file=/var/log/pmc-safe-redirect-manager-export-csv.log
	 *
	 * @ticket PMCVIP-2194
	 */
	public function export_csv( $args = array(), $assoc_args = array() ) {

		/*
		 * Defaults
		 */
		$assoc_args['sleep'] = 1;
		$assoc_args['batch-size'] = 50;

		$this->_assoc_args_properties_mapping = array(
			'status_code' => 'status-code',
			'csv_file'    => 'csv-file',
			'log_file'    => 'log-file',
		);

		$this->_extract_common_args( $assoc_args );

		if ( empty( $this->log_file ) || empty( $this->csv_file ) ) {
			$this->_error( 'File paths for both Log and CSV need to be specified before this command can be run' );
			return;
		}

		if ( ! class_exists( 'SRM_Safe_Redirect_Manager' ) ) {
			$this->_error( 'Safe Redirect Manager plugin needs to be activated before this command can be run' );
			return;
		}

		$this->status_code = ( is_numeric( $this->status_code ) ) ? intval( $this->status_code ) : 'all';

		$offset = 0;
		$csv_data = array();

		$post_fetch_args = array(
			'posts_per_page'   => $this->batch_size,
			'offset'           => $offset,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => $GLOBALS['safe_redirect_manager']->redirect_post_type,
			'post_status'      => 'publish',
			'suppress_filters' => false,
		);

		$posts = get_posts( $post_fetch_args );

		if ( empty( $posts ) || is_wp_error( $posts ) ) {
			$this->_error( 'No redirect rules found in Safe Redirect Manager' );
			return;
		}

		$success_count = 0;
		$skip_count = 0;
		$total_posts_fetched = count( $posts );
		$batch = 1;
		$posts_start = 1;
		$posts_end = $total_posts_fetched;

		while( ! empty( $posts ) ) {

			$this->_write_log( sprintf( 'Starting Batch: %d - Posts: %d to %d', $batch, $posts_start, $posts_end ) );

			foreach ( $posts as $post ) {

				$post_meta = $this->_get_meta( $post->ID );

				if ( ( $this->status_code !== 'all' && $this->status_code !== $post_meta['_redirect_rule_status_code'] ) || ! empty( $post_meta['_redirect_rule_from_regex'] ) ) {
					$skip_count++;
					$this->_write_log( sprintf( 'Post ID: %d - skipped', $post->ID ) );
					continue;
				}

				$csv_data[] = array(
					$post_meta['_redirect_rule_from'],
					$post_meta['_redirect_rule_to'],
					$post_meta['_redirect_rule_status_code'],
				);

				$success_count++;
				$this->_write_log( sprintf( 'Post ID: %d - exported', $post->ID ) );

			}	//end posts loop

			$this->_write_log( sprintf( 'Ending Batch: %d - Posts: %d to %d', $batch, $posts_start, $posts_end ) );
			$this->_write_log( "\n" );

			/*
			 * Sleep for a bit after running through a batch
			 * and call $this->stop_the_insanity()
			 * to scrub the object cache
			 */
			if ( $this->sleep > 0 ) {

				$this->_write_log( sprintf( 'Sleeping for %d second...', $this->sleep ) );
				$this->_write_log( "\n" );

				$this->stop_the_insanity();
				sleep( $this->sleep );

			}

			$post_fetch_args['offset'] += $total_posts_fetched;

			$posts = get_posts( $post_fetch_args );

			if ( empty( $posts ) || is_wp_error( $posts ) ) {
				break;
			}

			$batch++;
			$posts_start += $total_posts_fetched;

			$total_posts_fetched = count( $posts );
			$posts_end += $total_posts_fetched;

		}	//end of batch loop

		$this->_write_log( sprintf( 'Rules Exported: %d', $success_count ) );
		$this->_write_log( sprintf( 'Rules Skipped: %d', $skip_count ) );
		$this->_write_log( sprintf( 'Total Rules Processed: %d', $posts_end ) );

		$this->_write_log( 'Writing CSV.....' );

		$result_csv = $this->write_to_csv(
			$this->csv_file,
			array(
				'From URL',
				'TO URL',
				'STATUS CODE',
			),
			$csv_data,
			null,
			'w'
		);

		if ( ! $result_csv ) {
			$this->_error( 'Error writing CSV' );
		} else {
			$this->_write_log( sprintf( 'CSV created: %s', $result_csv ) );
		}

	}	//export_csv()

	/**
	 * Delete all redirect rules
	 *
	 * @subcommand delete-all
	 *
	 * ## OPTIONS
	 *
	 * [--status-code=<status-code>]
	 * : Status code of rules which are to be deleted. If not specified or set as 'all', all rules are deleted.
	 *
	 * --log-file=<log-file>
	 * : Path/Filename to the log file
	 *
	 * ## EXAMPLES
	 *
	 *		wp pmc-safe-redirect-manager delete-all --status-code=301 --log-file=/var/log/pmc-safe-redirect-manager-delete-all.log
	 *
	 * @ticket PMCVIP-2194
	 */
	public function delete_all( $args = array(), $assoc_args = array() ) {

		/*
		 * Defaults
		 */
		$assoc_args['sleep'] = 1;
		$assoc_args['batch-size'] = 50;

		$this->_assoc_args_properties_mapping = array(
			'status_code' => 'status-code',
			'log_file'    => 'log-file',
		);

		$this->_extract_common_args( $assoc_args );

		if ( empty( $this->log_file ) ) {
			$this->_error( 'File path for Log need to be specified before this command can be run' );
			return;
		}

		if ( ! class_exists( 'SRM_Safe_Redirect_Manager' ) ) {
			$this->_error( 'Safe Redirect Manager plugin needs to be activated before this command can be run' );
			return;
		}

		$this->status_code = ( is_numeric( $this->status_code ) ) ? intval( $this->status_code ) : 'all';

		$offset = 0;

		$post_fetch_args = array(
			'posts_per_page'   => $this->batch_size,
			'offset'           => $offset,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => $GLOBALS['safe_redirect_manager']->redirect_post_type,
			'post_status'      => 'publish',
			'suppress_filters' => false,
		);

		$posts = get_posts( $post_fetch_args );

		if ( empty( $posts ) || is_wp_error( $posts ) ) {
			$this->_error( 'No redirect rules found in Safe Redirect Manager' );
			return;
		}

		$success_count = 0;
		$skip_count = 0;
		$total_posts_fetched = count( $posts );
		$batch = 1;
		$posts_start = 1;
		$posts_end = $total_posts_fetched;

		if ( $this->dry_run ) {
			$this->_write_log( 'Starting Dry Run for SRM Rule cleanup' );
		} else {
			$this->_write_log( 'Starting SRM Rule cleanup' );
		}

		while( ! empty( $posts ) ) {

			$this->_write_log( sprintf( 'Starting Batch: %d - Posts: %d to %d', $batch, $posts_start, $posts_end ) );

			foreach ( $posts as $post ) {

				$post_meta = $this->_get_meta( $post->ID );

				if ( ( $this->status_code !== 'all' && $this->status_code !== $post_meta['_redirect_rule_status_code'] ) || ! empty( $post_meta['_redirect_rule_from_regex'] ) ) {
					$skip_count++;
					$this->_write_log( sprintf( 'Post ID: %d - skipped', $post->ID ) );
					$this->_write_log( sprintf( 'From: "%s" To: "%s"', $post_meta['_redirect_rule_from'], $post_meta['_redirect_rule_to'] ) );
					continue;
				}

				if ( ! $this->dry_run ) {
					wp_delete_post( $post->ID );
				}

				$success_count++;
				$this->_write_log( sprintf( 'Post ID: %d - deleted', $post->ID ) );
				$this->_write_log( sprintf( 'From: "%s" To: "%s"', $post_meta['_redirect_rule_from'], $post_meta['_redirect_rule_to'] ) );

			}	//end posts loop

			$this->_write_log( sprintf( 'Ending Batch: %d - Posts: %d to %d', $batch, $posts_start, $posts_end ) );
			$this->_write_log( "\n" );

			/*
			 * Sleep for a bit after running through a batch
			 * and call $this->stop_the_insanity()
			 * to scrub the object cache
			 */
			if ( $this->sleep > 0 ) {

				$this->_write_log( sprintf( 'Sleeping for %d second...', $this->sleep ) );
				$this->_write_log( "\n" );

				$this->stop_the_insanity();
				sleep( $this->sleep );

			}

			$post_fetch_args['offset'] += $total_posts_fetched;

			$posts = get_posts( $post_fetch_args );

			if ( empty( $posts ) || is_wp_error( $posts ) ) {
				break;
			}

			$batch++;
			$posts_start += $total_posts_fetched;

			$total_posts_fetched = count( $posts );
			$posts_end += $total_posts_fetched;

		}	//end of batch loop

		$this->_write_log( sprintf( 'Rules Deleted: %d', $success_count ) );
		$this->_write_log( sprintf( 'Rules Skipped: %d', $skip_count ) );
		$this->_write_log( sprintf( 'Total Rules Processed: %d', $posts_end ) );

		if ( $this->dry_run ) {
			$this->_write_log( 'Dry Run for SRM Rule cleanup completed' );
		} else {
			$this->_write_log( 'SRM Rule cleanup completed' );
		}

	}	//delete_all()

}	//end class


//EOF
