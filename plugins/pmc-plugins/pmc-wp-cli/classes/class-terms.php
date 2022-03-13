<?php
/**
 * WP-CLI command to manage terms
 *
 * @version 2017-08-23 CDWE-587
 *
 * @author Chandra Patel <chandrakumar.patel@rtcamp.com>
 *
 * @package pmc-wp-cli
 */

namespace PMC\WP_CLI;

/**
 * Class Terms
 *
 * @package PMC\WP_CLI
 */
class Terms extends \PMC_WP_CLI_Base {
	/**
	 *
	 * WP-CLI command to delete terms given in csv file
	 *
	 * ## OPTIONS
	 *
	 * [--taxonomy]
	 * : Taxonomy slug of which terms will delete.
	 *
	 * [--csv=<file-path>]
	 * : Path to the CSV file contain list of terms to delete. First column will contain terms slug.
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run.
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
	 *      wp pmc-terms delete-empty-from-csv --taxonomy=category --csv=/path/to/csv-file/ --url=vip.local
	 *
	 * @subcommand delete_empty_from_csv
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function delete_empty_from_csv( $args = array(), $assoc_args = array() ) {

		$this->_extract_common_args( $assoc_args );

		if ( $this->dry_run ) {
			$this->_warning( 'You have called the command pmc-terms:delete-empty in dry run mode.' . "\n" );
		}

		if ( empty( $assoc_args['taxonomy'] ) ) {
			$this->_error( 'Please pass taxonomy slug.' );
		}

		$taxonomy = sanitize_title( $assoc_args['taxonomy'] );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			$this->_error( sprintf( 'Taxonomy %s does not exists.', $taxonomy ) );
		}

		if ( empty( $assoc_args['csv'] ) ) {
			$this->_error( 'Please pass CSV file path.' );
		}

		if ( ! file_exists( $assoc_args['csv'] ) || ! is_readable( $assoc_args['csv'] ) ) {
			$this->_error( 'Either CSV file does not exists or is not readable.' );
		}

		$handle = fopen( $assoc_args['csv'], 'r' );

		if ( false === $handle ) {
			$this->_error( 'Please pass a valid .csv file.' );
		}

		$num_of_terms_deleted = 0;

		$iteration_count = 0;

		while ( ( $row = fgetcsv( $handle, 1000, ',' ) ) !== false ) {

			$iteration_count++;

			if ( empty( $row[0] ) ) {
				continue;
			}

			if ( empty( wpcom_vip_term_exists( $row[0], $taxonomy ) ) ) {

				$this->_warning( sprintf( '%s term does not exists.', $row[0] ) );
				$this->_write_log( '' ); // To add blank line.
				continue;

			}

			$term = get_term_by( 'slug', $row[0], $taxonomy );

			if ( 0 !== $term->count ) {

				$this->_warning( sprintf( '%s term is not empty.', $row[0] ) );
				$this->_write_log( '' ); // To add blank line.
				continue;

			}

			if ( ! $this->dry_run ) {
				wp_delete_term( $term->term_id, $taxonomy );
			}

			$num_of_terms_deleted++;

			$this->_success( sprintf( '%s term has been deleted.', $row[0] ) );

			$this->_write_log( '' ); // To add blank line.

			if ( 100 === $iteration_count ) {
				// To prevent execution of command due to memory issue.
				$this->stop_the_insanity();

				sleep( 2 );
				$iteration_count = 0;
			}

		}

		fclose( $handle );

		$this->stop_the_insanity();

		$this->_success( sprintf( 'Taxonomy: %s -- Number of terms deleted %d', $taxonomy, $num_of_terms_deleted ) );

		$this->_notify_done( 'WP-CLI command pmc-terms:delete-empty Completed' );

	}

	/**
	 *
	 * WP-CLI command to recalculate number of posts assigned to each term
	 *
	 * Note: No dry-run for this command. It's update only term count.
	 *
	 * ## OPTIONS
	 *
	 * <taxonomy>
	 * : Taxonomy to recalculate.
	 *
	 * ## EXAMPLES
	 *
	 *      wp pmc-terms recount post_tag --url=example.com
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function recount( $args = [], $assoc_args = [] ) {

		if ( empty( $args[0] ) ) {
			$this->_error( 'Please pass taxonomy.' );
		}

		$taxonomy = $args[0];

		if ( ! taxonomy_exists( $taxonomy ) ) {
			$this->_error( sprintf( 'Taxonomy %s does not exist.', $taxonomy ) );
		}

		$term_count = wp_count_terms(
			$taxonomy,
			[
				'hide_empty' => false,
			]
		);

		if ( empty( $term_count ) ) {
			$this->_error( 'No terms found.' );
		}

		$this->_write_log( sprintf( 'Total %d terms found.', $term_count ) );

		$limit = 100;

		$iteration = ceil( $term_count / $limit );

		for ( $i = 0; $i < $iteration; $i++ ) {

			$args = [
				'taxonomy'   => [ $taxonomy ],
				'hide_empty' => false,
				'number'     => $limit,
				'offset'     => ( $i * $limit ),
			];

			$term_query = new \WP_Term_Query();

			$terms = $term_query->query( $args );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				continue;
			}

			$term_taxonomy_ids = wp_list_pluck( $terms, 'term_taxonomy_id' );

			wp_update_term_count( $term_taxonomy_ids, $taxonomy );

			$this->_write_log( sprintf( 'Batch %d -- terms count updated.', ( $i + 1 ) ) );

			$this->stop_the_insanity();

			sleep( 1 );

		}

		$this->_success( sprintf( 'Updated %s term count.', $taxonomy ) );

	}

	/**
	 *
	 * WP-CLI command to delete terms where any terms with zero content
	 *
	 * ## OPTIONS
	 *
	 * [--taxonomy]
	 * : Taxonomy slug of which empty terms will delete.
	 * ---
	 * default: post_tag
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run.
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
	 *      wp pmc-terms delete-empty-terms --url=example.com
	 *
	 * @subcommand delete-empty-terms
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function delete_empty_terms( $args = [], $assoc_args = [] ) {

		$this->_extract_common_args( $assoc_args );

		if ( $this->dry_run ) {
			$this->_warning( 'You have called the command pmc-terms:delete_empty_terms in dry run mode.' . "\n" );
		}

		$taxonomy = ( ! empty( $assoc_args['taxonomy'] ) ) ? $assoc_args['taxonomy'] : 'post_tag';

		if ( ! taxonomy_exists( $taxonomy ) ) {
			$this->_error( sprintf( 'Taxonomy %s does not exist.', $taxonomy ) );
		}

		$term_count = wp_count_terms(
			$taxonomy,
			[
				'hide_empty' => false,
			]
		);

		if ( empty( $term_count ) ) {
			$this->_error( 'No terms found.' );
		}

		$this->_write_log( sprintf( 'Total %d terms found.', $term_count ) );

		wp_defer_term_counting( true );

		$limit = 100;

		$iteration = ceil( $term_count / $limit );

		$num_of_terms_deleted = 0;

		$num_of_terms_deleted_in_iteration = 0;

		for ( $i = 0; $i < $iteration; $i++ ) {

			$args = [
				'taxonomy'   => [ $taxonomy ],
				'hide_empty' => false,
				'number'     => $limit,
			];

			// total terms count will change after terms delete in each iteration.
			// So, need to deduct no of terms deleted in each iteration from offset.
			$args['offset'] = ( $i * $limit ) - $num_of_terms_deleted_in_iteration;

			$term_query = new \WP_Term_Query();

			$terms = $term_query->query( $args );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

				foreach ( $terms as $term ) {

					if ( 0 === $term->count ) {

						$this->_write_log( sprintf( '%s term has no posts.', $term->slug ) );

						if ( ! $this->dry_run ) {

							$action = wp_delete_term( $term->term_id, $taxonomy );

							if ( true === $action && ! is_wp_error( $action ) ) {
								$num_of_terms_deleted_in_iteration++;
								$this->_success( sprintf( '%s term has been deleted.', $term->slug ) );
								$this->_write_log( "\n" );
								$num_of_terms_deleted++;
							}
						}
					}
				}

				$this->stop_the_insanity();

				sleep( 2 );
			}
		}

		wp_defer_term_counting( false );

		$this->_success( sprintf( 'Taxonomy: %s -- Number of terms deleted %d', $taxonomy, $num_of_terms_deleted ) );

		$this->_notify_done( 'WP-CLI command pmc-terms:delete_empty_terms Completed' );

	}

	/**
	 *
	 * WP-CLI command to delete terms where any terms with 1-3 articles that are older then 1 year
	 *
	 * ## OPTIONS
	 *
	 * [--taxonomy]
	 * : Taxonomy slug of which empty terms will delete.
	 * ---
	 * default: post_tag
	 *
	 * [--post_type]
	 * : Multiple post types slug with comma separated.
	 * ---
	 * default: post
	 *
	 * [--post-created-before=<date>]
	 * : Tag to remove, if the post is created before this date. date format: m-d-Y
	 * ---
	 * default: 12-01-2019
	 *
	 * [--number-of-posts]
	 * : Number of posts assigned to tag
	 * ---
	 * default: 3
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run.
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
	 * wp pmc-terms delete-terms-with-old-content --url=example.com
	 *
	 * @subcommand delete-terms-with-old-content
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function delete_terms_with_old_content( $args = [], $assoc_args = [] ) {

		$this->_extract_common_args( $assoc_args );

		if ( $this->dry_run ) {
			$this->_warning( 'You have called the command pmc-terms:delete_terms_with_old_content in dry run mode.' . "\n" );
		}

		$taxonomy                         = ! empty( $assoc_args['taxonomy'] ) ? $assoc_args['taxonomy'] : 'post_tag';
		$number_of_posts                  = ! empty( $assoc_args['number-of-posts'] ) ? $assoc_args['number-of-posts'] : 3;
		$post_created_before              = ! empty( $assoc_args['post-created-before'] ) ? $assoc_args['post-created-before'] : gmdate( 'm-d-Y', strtotime( '-1 year' ) );
		$post_created_before_in_timestamp = \DateTime::createFromFormat( 'm-d-Y', $post_created_before )->getTimestamp();

		if ( ! taxonomy_exists( $taxonomy ) ) {
			$this->_error( sprintf( 'Taxonomy %s does not exist.', $taxonomy ) );
		}

		if ( ! empty( $assoc_args['post_type'] ) ) {
			$post_types = explode( ',', $assoc_args['post_type'] );
		} else {
			$post_types = [ 'post' ];
		}

		$term_count = wp_count_terms(
			$taxonomy,
			[
				'hide_empty' => true,
			]
		);

		if ( empty( $term_count ) ) {
			$this->_error( 'No terms found.' );
		}

		$this->_write_log( sprintf( 'Total %d terms found.', $term_count ) );

		wp_defer_term_counting( true );

		$limit = 100;

		$iteration = ceil( $term_count / $limit );

		$num_of_terms_deleted = 0;

		$num_of_terms_deleted_in_iteration = 0;

		for ( $i = 0; $i < $iteration; $i++ ) {

			$args = [
				'taxonomy'   => [ $taxonomy ],
				'hide_empty' => true,
				'number'     => $limit,
			];

			// total terms count will change after terms delete in each iteration.
			// So, need to deduct no of terms deleted in iteration from offset.
			$args['offset'] = ( $i * $limit ) - $num_of_terms_deleted_in_iteration;

			$term_query = new \WP_Term_Query();

			$terms = $term_query->query( $args );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

				foreach ( $terms as $term ) {
					if ( $term->count > $number_of_posts || $term->count < 1 ) {
						continue;
					}

					$can_delete = false;
					$post_args  = $this->_get_post_args( $post_types, $term, $taxonomy );
					$posts      = get_posts( $post_args ); // phpcs:ignore

					if ( ! empty( $posts ) ) {
						$post = array_pop( $posts );

						if ( strtotime( $post->post_date_gmt ) < $post_created_before_in_timestamp ) {
							$can_delete = true;
							$this->_write_log( sprintf( '%s term has total %d posts older than %s.', $term->slug, $term->count, $post_created_before ) );
						}
					}

					if ( ! $this->dry_run && $can_delete ) {

						$action = wp_delete_term( $term->term_id, $taxonomy );

						if ( true === $action && ! is_wp_error( $action ) ) {

							$num_of_terms_deleted_in_iteration++;
							$this->_success( sprintf( '%s term has been deleted.', $term->slug ) );
							$this->_write_log( "\n" );
							$num_of_terms_deleted++;
						}
					}
				}

				$this->stop_the_insanity();

				sleep( 2 );
			}
		}

		wp_defer_term_counting( false );

		$this->_success( sprintf( 'Taxonomy: %s -- Number of terms deleted %d', $taxonomy, $num_of_terms_deleted ) );

		$this->_notify_done( 'WP-CLI command pmc-terms:delete_terms_with_old_content Completed' );

	}

	/**
	 * Assign new term to posts given in CSV file
	 *
	 * ## OPTIONS
	 *
	 * [--taxonomy]
	 * : Taxonomy slug of which terms to assign.
	 *
	 * [--csv=<file-path>]
	 * : The CSV file contain list of post IDs, term.
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run.
	 * ---
	 * default: true
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
	 *      wp pmc-terms assign-term-from-csv --taxonomy=category --csv=/path/to/csv-file/ --url=vip.local
	 *
	 * @subcommand assign_term_from_csv
	 *
	 * @param array $args Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function assign_term_from_csv( $args = [], $assoc_args = [] ) {

		$this->_extract_common_args( $assoc_args );

		if ( $this->dry_run ) {
			$this->_write_log( 'NOTICE: You have called the command pmc-terms::assign-term-from-csv in dry run mode.' . "\n" );
		}

		if ( empty( $assoc_args['taxonomy'] ) ) {
			$this->_error( 'Please pass taxonomy slug.' );
		}

		$taxonomy = sanitize_title( $assoc_args['taxonomy'] );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			$this->_error( sprintf( 'Taxonomy %s does not exists.', $taxonomy ) );
		}

		if ( ! empty( $assoc_args['csv'] ) && file_exists( $assoc_args['csv'] ) && is_readable( $assoc_args['csv'] ) ) {

			$handle = fopen( $assoc_args['csv'], 'r' ); //@codingStandardsIgnoreLine: WP_Filesystem not appropriate for this logic.

			if ( false !== $handle ) {

				wp_defer_term_counting( true );

				$num_of_terms_assigned = 0;
				$iteration_count       = 0;

				while ( false !== ( $row = fgetcsv( $handle, 1000, ',' ) ) ) { // phpcs:ignore

					$iteration_count ++;

					list( $post_id, $term_slug ) = $row;

					$post_id = intval( $post_id );

					if ( empty( $post_id ) || empty( $term_slug ) ) {

						$this->_write_log( 'Post ID|term data not passed.' );
						$this->_write_log( '' );
						continue;

					}

					$post_object = get_post( $post_id );

					if ( empty( $post_object ) ) {

						$this->_write_log( $post_id . ' post not found.' );
						$this->_write_log( '' );
						continue;
					}

					$this->_write_log( 'Post Found: ' . $post_object->ID . ' | ' . $post_object->post_type . ' | ' . $post_object->post_title );

					if ( empty( wpcom_vip_term_exists( $term_slug, $taxonomy ) ) ) {

						$this->_warning( sprintf( '%s term does not exists.', $term_slug ) );
						$this->_write_log( '' ); // To add blank line.
						continue;

					}

					$term = get_term_by( 'slug', $term_slug, $taxonomy );

					if ( is_a( $term, 'WP_Term' ) ) {
						$this->_write_log( 'Term Found: ' . $term->slug );
					}

					if ( ! $this->dry_run ) {
						wp_set_object_terms( $post_object->ID, [ $term->slug ], $taxonomy, true );
						$this->_write_log( 'New term have been assigned to post.' . "\n" );
					} else {
						$this->_write_log( 'New term will be assigned to post.' . "\n" );
					}

					$num_of_terms_assigned ++;

					if ( 100 === $iteration_count ) {
						// To prevent execution of command due to memory issue.
						$this->stop_the_insanity();

						sleep( 2 );
						$iteration_count = 0;
					}

				} //End while loop.

				wp_defer_term_counting( false );

				$this->stop_the_insanity();

				$this->_success( sprintf( ':: Number of terms assigned %d ::', $num_of_terms_assigned ) );

				$this->_notify_done( 'WP-CLI command pmc-terms:assign-term-from-csv Completed' );

			}

			fclose( $handle ); //@codingStandardsIgnoreLine: WP_Filesystem not appropriate for this logic.

		} else {
			$this->_error( 'Either CSV file does not exists or is not readable.' );
		}
	}

	/**
	 * WP-CLI command to generate CSV report of all remaining tags, with tag name, date and number of posts they have attached.
	 *
	 * ## OPTIONS
	 *
	 * [--file=<file>]
	 * : If provided then will write to a CSV file on the server.
	 *
	 * [--taxonomy]
	 * : Taxonomy slug of which CSV report will be created for.
	 * ---
	 * default: post_tag
	 *
	 * [--post_type]
	 * : Multiple post types slug with comma separated.
	 * ---
	 * default: post
	 *
	 * [--dry-run]
	 * : Whether or not to do dry run.
	 * ---
	 * default: true
	 * options:
	 *   - true
	 *   - false
	 *
	 * [--email]
	 * : Email to send notification after script complete.
	 *
	 * [--email-when-done]
	 * : Whether to send notification or not. Only applicable once you run `wp pmc-terms generate-csv --file=report.csv
	 *
	 * [--log-file=<file>]
	 * : Path to the log file.
	 *
	 * [--email-logfile]
	 * : Whether to send log file or not.
	 *
	 * ## EXAMPLES
	 *
	 * wp pmc-terms generate-csv                       This will output the content in CSV format.
	 * wp pmc-terms generate-csv --quiet > report.csv  This will generate report.csv file on your local machine.
	 * wp pmc-terms generate-csv --file=report.csv     This will generate report.csv file on the remote server.
	 *
	 * @subcommand generate-csv
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @return void
	 */
	public function generate_csv( $args = [], $assoc_args = [] ): void {
		$this->_extract_common_args( $assoc_args );
		$this->_notify_start( 'Start: generate-csv' );

		$csv_file    = ! empty( $assoc_args['file'] ) ? $assoc_args['file'] : 'php://output';
		$taxonomy    = ! empty( $assoc_args['taxonomy'] ) ? $assoc_args['taxonomy'] : 'post_tag';
		$post_types  = ! empty( $assoc_args['post_type'] ) ? explode( ',', $assoc_args['post_type'] ) : [ 'post' ];
		$terms_count = $this->_get_terms_count( $taxonomy );

		if ( empty( $terms_count ) ) {
			$this->_error( 'No terms found' );
		}

		$echo_to_standard_output = 'php://output' === $csv_file;
		$progress                = null;

		if ( ! $echo_to_standard_output ) {
			$progress = \WP_CLI\Utils\make_progress_bar( 'Generating CSV:', $terms_count );
		}

		$this->_write_log( sprintf( 'Total %d terms found.', $terms_count ) );

		if ( $echo_to_standard_output ) {
			// As we're echoing CSV to standard output, let's disable the `--dry-run` check.
			$this->dry_run = false;
		}

		if ( ! $this->dry_run ) {
			$this->write_to_csv(
				$csv_file,
				[
					'name',
					'date',
					'number_of_posts',
				],
				[ [] ],
			);
		}

		$offset = 0;

		do {
			if ( ! $echo_to_standard_output ) {
				$progress->tick();
			}

			$terms   = $this->_get_terms( $taxonomy, $offset );
			$offset += $this->batch_size;

			foreach ( $terms as $term ) {
				$post_args = $this->_get_post_args( $post_types, $term, $taxonomy );
				$posts     = get_posts( $post_args ); // phpcs:disable

				if ( ! $this->dry_run && ! empty( $posts ) ) {
					$post = reset( $posts );
					$this->write_to_csv(
						$csv_file,
						[],
						[
							[
								$term->slug,
								date_i18n( get_option( 'date_format' ), strtotime( $post->post_date_gmt ) ),
								$term->count
							]
						],
						null,
						'a',
					);
				}
			}

			$this->_update_iteration();
		} while (
			! empty( $terms )
		);

		if ( ! $echo_to_standard_output ) {
			$progress->finish();
		}

		$this->_notify_done( 'WP-CLI command pmc-terms:generate-csv completed', [ $csv_file ] );
	}

	/**
	 * Get terms count
	 *
	 * @param string $taxonomy
	 *
	 * @return int
	 */
	private function _get_terms_count( string $taxonomy ): int {
		$term_count = wp_count_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]
		);

		if ( is_wp_error( $term_count ) ) {
			return 0;
		}

		return (int) $term_count;
	}

	/**
	 * Get the terms
	 *
	 * @param string $taxonomy
	 * @param int    $offset
	 *
	 * @return array
	 */
	private function _get_terms( string $taxonomy, int $offset = 0 ): array {
		$args = apply_filters(
			'pmc_wp_cli_terms_get_terms_args',
			[
				'taxonomy'   => [ $taxonomy ],
				'hide_empty' => false,
				'number'     => $this->batch_size,
				'offset'     => $offset,
			]
		);

		return (array) ( new \WP_Term_Query() )->query( $args );
	}

	/**
	 * Get post args
	 *
	 * @param array $post_types
	 * @param object $term
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	private function _get_post_args( array $post_types, object $term, string $taxonomy ): array {
		$args = [
			'post_type'        => $post_types,
			'numberposts'      => 1,
			'suppress_filters' => false,
			'order_by'         => 'date',
			'order'            => 'desc',
		];

		if ( 'post_tag' === $taxonomy ) {
			$args['tag_id'] = $term->term_id;
		}

		if ( 'category' === $taxonomy ) {
			$args['category'] = $term->term_id;
		}

		if ( ! in_array( $taxonomy, [ 'post_tag', 'category' ], true ) ) {
			$args['tax_query'] = [ // phpcs:ignore
				[
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				],
			];
		}

		return apply_filters( 'pmc_wp_cli_terms_get_post_args', $args );
	}
}

//EOF