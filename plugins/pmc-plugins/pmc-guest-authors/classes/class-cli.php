<?php
namespace PMC\Guest_Authors;

/**
 * Contains CLI commands for PMC Guest Authors.
 */
class CLI extends \PMC_WP_CLI_Base {

	/**
	 *
	 * WP-CLI command to add meta to posts
	 *
	 * ## OPTIONS
	 *
	 * [--meta-key]
	 * : Meta key to add
	 *
	 * [--meta-value]
	 * : Meta value to add
	 *
	 * [--csv-file]
	 * : CSV file containing post ids
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run
	 * ---
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 *
	 * [--log-file=<file>]
	 * : Path to the log file.
	 *
	 * [--email]
	 * : Email to send notification after script complete.
	 *
	 * [--email-when-done]
	 * : Whether to send notification or not.
	 *
	 * [--email-logfile]
	 * : Whether to send log file or not.
	 *
	 * ## EXAMPLES
	 *
	 *      $ wp pmc-guest-authors add_meta_to_guest_authors_from_csv --meta-key="test" --meta-value="test-value" --csv-file="authors.csv"
	 *
	 * @subcommand add_meta_to_guest_authors_from_csv
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function add_meta_to_guest_authors_from_csv( array $args = [], array $assoc_args = [] ) : void {
		global $coauthors_plus;

		$this->_extract_common_args( $assoc_args );

		if (
			empty( $coauthors_plus )
			|| ! isset( $coauthors_plus->guest_authors )
		) {
			$this->_error( 'Something went wrong with Co-Authors Plus plugin!' );
			return;
		}

		if ( $this->dry_run ) {
			$this->_warning( 'You have called the command wp pmc-guest-authors add_meta_to_guest_authors_from_csv in dry run mode.' . PHP_EOL );
			$this->_write_log( '------------------------------------------------------------------------------', 0 );
		}

		if ( empty( $assoc_args['meta-key'] ) ) {
			$this->_error( 'Please pass meta key.' );
			return;
		}

		if ( ! isset( $assoc_args['meta-value'] ) ) {
			$this->_error( 'Please pass meta value.' );
			return;
		}

		if ( empty( $assoc_args['csv-file'] ) ) {
			$this->_error( 'Please pass csv file.' );
			return;
		}

		if ( ! file_exists( $assoc_args['csv-file'] ) ) {
			$this->_error( 'Given .csv file does not exists.' );
			return;
		}

		$this->_notify_start( 'WP-CLI command wp pmc-meta add_meta_to_post_from_csv: Started' );
		$this->_write_log( '------------------------------------------------------------------------------', 0 );

		$meta_key     = sanitize_key( $assoc_args['meta-key'] );
		$meta_value   = $assoc_args['meta-value'];
		$csv_file     = $assoc_args['csv-file'];
		$count        = 0;
		$post_updated = 0;
		$failed_count = 0;

		try {
			$file = new \SplFileObject( $csv_file, 'r' );
		} catch ( \Exception $e ) {

			$this->_error( 'Please pass a valid .csv file in command.' );
			return;

		}

		$file->setFlags( \SplFileObject::READ_CSV );

		foreach ( $file as $row ) {

			$count++;

			if ( 0 === ( $count % 100 ) ) {
				$this->stop_the_insanity();
				sleep( 2 );
			}

			if ( count( $row ) < 2 ) {
				$this->_warning( sprintf( 'Skipped unknown author at line %d; Invalid data', $count ) );
				$failed_count++;
				continue;
			}

			$post_name = $row[0];
			$post_url  = $row[1];

			// Skip the iteration if post url is missing.
			if ( empty( $post_url ) ) {
				$this->_warning( sprintf( 'Skipped author (%s) at line %d; Invalid data', $post_name, $count ) );
				$failed_count++;
				continue;
			}

			// Retrieve post using the title.
			$post = $coauthors_plus->guest_authors->get_guest_author_by( 'post_name', basename( untrailingslashit( $post_url ) ) );

			// skip iteration if guest author id not found.
			if ( empty( $post ) ) {
				$this->_warning( sprintf( 'Skipped author (%s); couldn\'t find author', $post_name ) );
				$failed_count++;
				continue;
			}

			if ( ! $this->dry_run ) {
				$added = update_post_meta( $post->ID, $meta_key, $meta_value );

				if ( false !== $added ) {
					$this->_success( sprintf( 'Meta (%s) is added to author %d (%s)', $meta_key . ' => ' . $meta_value, $post->ID, $post_name ) );
					$post_updated++;
				} else {
					$failed_count++;
					$this->_warning( sprintf( 'Failed adding meta to author %d (%s)', $post->ID, $post_name ) );
					continue;
				}

			} else {
				$this->_success( sprintf( 'Meta (%s) will be added to author %d (%s)', $meta_key . ' => ' . $meta_value, $post->ID, $post_name ) );
			}

		} // foreach.

		unset( $file ); // required, to close file stream.

		if ( ! $this->dry_run ) {
			$this->_write_log( '------------------------------------------------------------------------------', 0 );
			$this->_success( sprintf( 'Total authors processed %d', $count ) );
			$this->_success( sprintf( 'Total authors skipped   %d', $failed_count ) );
			$this->_success( sprintf( 'Total authors updated   %d', $post_updated ) );
		} else {
			$this->_write_log( '------------------------------------------------------------------------------', 0 );
			$this->_success( sprintf( 'Total authors processed       %d', $count ) );
			$this->_success( sprintf( 'Total authors will be skipped %d', $failed_count ) );
			$this->_success( sprintf( 'Total authors will be updated %d', $post_updated ) );
		}

		$this->_write_log( '------------------------------------------------------------------------------', 0 );
		$this->_notify_done( 'WP-CLI command wp pmc-guest-authors add_meta_to_guest_authors_from_csv Completed' );
	}

}

//EOF
